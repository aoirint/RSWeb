import os
import sqlite3
from datetime import datetime as dt
from datetime import timedelta

import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.dates as mdates
import matplotlib.ticker as ticker


db = sqlite3.connect('./data/db.sqlite3')
cur = db.cursor()

now = dt.now()
today = now.replace(hour=0, minute=0, second=0, microsecond=0)

def getData(start, end):
    rows = []
    for row in cur.execute('SELECT * FROM gpu'):
        tsString = row[13]
        ts = dt.strptime(tsString, '%Y-%m-%d %H:%M:%S')
        if start <= ts and ts < end:
            rows.append(row + (ts, ))
    return rows

def draw(date, fp):
    start = date ; end = date + timedelta(days=1)
    x = [] ; y = []
    for row in getData(start, end):
        ts = row[14] ; raw = row[10]

        val = int(raw[:-4])
        x.append(ts) ; y.append(val)

    dateString = start.date().isoformat()

    plt.clf()
    fig, ax = plt.subplots()
    ax.set_xlim(start, end)
    ax.set_ylim(0, 3500)
    ax.plot(x, y)
    ax.plot([ start, end ], [ 3072, ] * 2)
#    ax.plot([ start, end ], [ 10 * 10**9, ] * 2)
    ax.xaxis.set_major_formatter(mdates.DateFormatter('%H:%M'))
    ax.yaxis.set_major_formatter(ticker.FuncFormatter(lambda y, pos: '%d MiB' % (y, )))
    fig.suptitle(dateString)
    fig.savefig(fp)

graph_dir = 'static/graph/gpumem'
os.makedirs(graph_dir, exist_ok=True)

for dd in range(1):
    date = today - timedelta(days=dd)
    draw(date, os.path.join(graph_dir, '%s.png' % date.date().isoformat()))
