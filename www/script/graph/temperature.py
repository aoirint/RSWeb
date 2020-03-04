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
    for row in cur.execute('SELECT * FROM sensor'):
        tsString = row[3]
        ts = dt.strptime(tsString, '%Y-%m-%d %H:%M:%S')
        if start <= ts and ts < end:
            rows.append(row + (ts, ))
    return rows

def draw(date, fp):
    start = date ; end = date + timedelta(days=1)
    x = [] ; y = []
    for row in getData(start, end):
        ts = row[4] ; raw = row[2]

        # #define VOLT 5.0
        # #define V_OFFSET 0.6
        # #define DEGREE_PER_VOLT 0.01
        #
        # float v0 = (float)temperatureRaw / 1023 * VOLT;
        # float temperature = (v0 - V_OFFSET) / DEGREE_PER_VOLT;
        val = ((raw/1023*5.0)-0.6)/0.01
        val -= 11.3 # hand-fix

        x.append(ts) ; y.append(val)

    dateString = start.date().isoformat()

    plt.clf()
    fig, ax = plt.subplots()
    ax.set_xlim(start, end)
    ax.set_ylim(0, 40)
    ax.plot(x, y)
#    ax.plot([ start, end ], [ 10 * 10**9, ] * 2)
    ax.xaxis.set_major_formatter(mdates.DateFormatter('%H:%M'))
    ax.yaxis.set_major_formatter(ticker.FuncFormatter(lambda y, pos: '%.2f C' % (y, )))
    fig.suptitle(dateString)
    fig.savefig(fp)

graph_dir = 'static/graph/temperature'
os.makedirs(graph_dir, exist_ok=True)

for dd in range(1):
    date = today - timedelta(days=dd)
    draw(date, os.path.join(graph_dir, '%s.png' % date.date().isoformat()))
