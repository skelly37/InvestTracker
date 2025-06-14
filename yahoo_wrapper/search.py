from dataclasses import dataclass
from datetime import datetime
from typing import (
    Dict,
    List,
)

import yfinance as yf


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