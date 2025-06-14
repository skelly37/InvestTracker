from dataclasses import asdict

from flask import Flask, request

from .search import *


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
        quote = Quote.fromSymbol(symbol)
        return asdict(quote)

    
    app.run(host='0.0.0.0', port=5000, debug=False)


server()