from dataclasses import dataclass
from enum import Enum

from yfinance import Ticker


@dataclass(frozen=True)
class TickerInfo:
    name:         str
    symbol:       str
    currentPrice: float

    exchange:      str
    currency:      str
    previousClose: float
    openPrice:     float

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
    Quarter   = ("3mo", "1d")
    Year      = ("1y",  "1wk")
    FiveYears = ("5y",  "1mo")
    Max       = ("max", "3mo")

    @property
    def period(self):
        return self.value[0]

    @property
    def interval(self):
        return self.value[1]

    @staticmethod
    def get_time_scale(time_scale: str) -> "TimeScale":
        for time_scale_enum in TimeScale:
            if time_scale_enum.period == time_scale:
                return time_scale_enum
        raise ValueError(f"Invalid time scale: {time_scale}")


def get_info(ticker: Ticker) -> TickerInfo:
    info = ticker.info

    return TickerInfo(
        name=info["longName"],
        exchange=info["fullExchangeName"],
        symbol=info["symbol"],
        currency=info["currency"],
        previousClose=info.get("previousClose", 0),
        openPrice=info.get("open", 0),
        currentPrice=info.get("currentPrice", info["regularMarketPrice"]),
        priceToBook=info.get("priceToBook", 0),
        returnOnAssets=info.get("returnOnAssets", 0),
        returnOnEquity=info.get("returnOnEquity", 0),
        enterpriseToEbitda=info.get("enterpriseToEbitda", 0),
        marketCap=info.get("marketCap", 0),
        sharesOutstanding=info.get("sharesOutstanding", 0),
        totalRevenue=info.get("totalRevenue", 0),
        financialCurrency=info.get("financialCurrency", 0)
    )


def get_history(ticker: Ticker, time_scale: TimeScale = TimeScale.Month) -> dict:
    df = ticker.history(period=time_scale.period, interval=time_scale.interval)["Close"]

    return {int(timestamp.timestamp()): close for timestamp, close in df.items()}