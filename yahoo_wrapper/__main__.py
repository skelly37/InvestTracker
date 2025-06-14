from dataclasses import asdict
from urllib.parse import unquote

from flask import Flask, request

from .search import *
from .ticker import *


def server():
    app = Flask(__name__)


    @app.route("/search")
    def search():
        query = request.args.get("q", "")

        result = {"results": []}

        for item in find_items(query):
            result["results"].append(asdict(item))

        return result
    
    
    @app.route("/quote")
    def quote():
        symbol = request.args.get("q", "")
        symbol = unquote(symbol)
        ticker = yf.Ticker(symbol)
        res = asdict(get_info(ticker))
        print(res)
        return res


    @app.route("/history")
    def history():
        symbol = request.args.get("q", "")
        interval = request.args.get("interval", "")
        symbol = unquote(symbol)
        ticker = yf.Ticker(symbol)

        res = get_history(ticker, TimeScale.get_time_scale(interval))

        print(res)
        return res

    
    app.run(host='0.0.0.0', port=5000, debug=False)


server()