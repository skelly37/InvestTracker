from dataclasses import dataclass
from enum import Enum

from yfinance import Ticker


@dataclass(frozen=True)
class TickerInfo:
    name:         str
    exchange:     str
    symbol:       str
    currency:     str
    currentPrice: float

    priceToBook:        float
    returnOnAssets:     float
    returnOnEquity:     float
    enterpriseToEbitda: float

    marketCap:         float
    sharesOutstanding: float
    totalRevenue:      float
    financialCurrency: float


class TimeScale(Enum):
    Day       = ("1d",  "15m")
    Week      = ("5d",  "1h")
    Month     = ("1mo", "1d")
    Quarter   = ("3mo", "1d")  # skip to 3d in post-process
    Year      = ("1y",  "1wk")
    FiveYears = ("5y",  "1mo")
    Max       = ("max", "3mo")

    @property
    def period(self):
        return self.value[0]

    @property
    def interval(self):
        return self.value[1]


def get_info(ticker: Ticker) -> TickerInfo:
    info = ticker.info

    return TickerInfo(
        name=info["longName"],
        exchange=info["fullExchangeName"],
        symbol=info["symbol"],
        currency=info["currency"],
        currentPrice=info["currentPrice"],
        priceToBook=info["priceToBook"],
        returnOnAssets=info["returnOnAssets"],
        returnOnEquity=info["returnOnEquity"],
        enterpriseToEbitda=info["enterpriseToEbitda"],
        marketCap=info["marketCap"],
        sharesOutstanding=info["sharesOutstanding"],
        totalRevenue=info["totalRevenue"],
        financialCurrency=info["financialCurrency"]
    )


def get_history(ticker: Ticker, time_scale: TimeScale = TimeScale.Month) -> dict:
    df = ticker.history(period=time_scale.period, interval=time_scale.interval)["Close"]

    return {int(timestamp.timestamp()): close for timestamp, close in df.items()}