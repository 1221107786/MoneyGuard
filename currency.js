// currency.js

function convertCurrency() {
    const amount = parseFloat(document.getElementById('amount').value);
    const fromCurrency = document.getElementById('from-currency').value;
    const toCurrency = document.getElementById('to-currency').value;
    const resultElement = document.getElementById('result');

    // Example exchange rates (these are just placeholder values)
    const exchangeRates = {
        USD: { EUR: 0.899, GBP: 0.758, USD: 1, MYR: 4.34 },
        EUR: { USD: 1.11, GBP: 0.843, EUR: 1, MYR: 4.82 },
        GBP: { USD: 1.32, EUR: 1.18, GBP: 1, MYR: 5.72 },
        MYR: { USD: 0.23, EUR: 0.20, GBP: 0.17, MYR: 1 }
    };
    
    

    if (!amount || isNaN(amount)) {
        resultElement.textContent = 'Please enter a valid amount.';
        return;
    }

    if (fromCurrency === toCurrency) {
        resultElement.textContent = `${amount} ${fromCurrency} is equal to ${amount} ${toCurrency}.`;
        return;
    }

    const rate = exchangeRates[fromCurrency][toCurrency];
    if (rate === undefined) {
        resultElement.textContent = 'Conversion rate not available.';
        return;
    }

    const convertedAmount = (amount * rate).toFixed(2);
    resultElement.textContent = `${amount} ${fromCurrency} is equal to ${convertedAmount} ${toCurrency}.`;
}
