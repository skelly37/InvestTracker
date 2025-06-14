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
        symbol = unquote(request.args.get("q", ""))
        ticker = yf.Ticker(symbol)
        return asdict(get_info(ticker))


    @app.route("/history")
    def history():
        symbol = unquote(request.args.get("q", ""))
        interval = request.args.get("interval", "")
        ticker = yf.Ticker(symbol)
        return get_history(ticker, TimeScale.get_time_scale(interval))

    
    app.run(host='0.0.0.0', port=5000, debug=False)


server()