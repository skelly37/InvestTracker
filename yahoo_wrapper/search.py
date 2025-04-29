from dataclasses import dataclass
from datetime import datetime
from typing import (
    Dict,
    List,
)

import yfinance as yf

from ticker import *


@dataclass(frozen=True)
class Quote:
    info: TickerInfo
    history: Dict[datetime, float]


    @staticmethod
    def fromSymbol(symbol: str) -> 'Quote':
        ticker = yf.Ticker(symbol)

        return Quote(info=get_info(ticker), history=get_history(ticker))


@dataclass(frozen=True)
class SearchResult:
    name:             str
    symbol:           str
    exchange:         str
    exchangeFullName: str


def find_items(query: str) -> List[SearchResult]:
    result = []

    for quote in yf.Search(query).quotes:
        result.append(SearchResult(
            name=quote["longname"],
            symbol=quote["symbol"],
            exchange=quote["exchange"],
            exchangeFullName=quote["exchDisp"]
        ))

    return result