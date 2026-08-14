<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Delivery Payment — InstaDrop Courier</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
        }
    </style>
</head>
<body class="bg-[#0a192f] text-slate-100 min-h-screen flex items-center justify-center p-4 sm:p-6">
    <div class="w-full max-w-xl bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl space-y-8">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-[#c6ff00] bg-[#c6ff00]/10 px-3 py-1 rounded-full border border-[#c6ff00]/30">
                    INSTADROP SECURE PAYMENT
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white font-display mt-2">
                    Invoice Checkout
                </h1>
            </div>
            <div class="text-right text-xs text-slate-400">
                <p>Invoice #: <strong class="text-white">{{ $invoice['invoice_number'] ?? 'INV-2026-001' }}</strong></p>
                <p>Date: {{ date('d M Y') }}</p>
            </div>
        </div>

        <!-- Order Summary Details -->
        <div class="bg-slate-950/70 rounded-2xl p-6 border border-slate-800 space-y-4 text-xs">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                <h3 class="text-sm font-bold text-white">Delivery Summary</h3>
                <!-- PDF Download Action Button -->
                <button onclick="window.print()" class="no-print text-[#c6ff00] hover:underline font-bold text-xs flex items-center gap-1">
                    📥 Download PDF Invoice Receipt
                </button>
            </div>
            
            <div class="grid grid-cols-2 gap-4 text-slate-300">
                <div>
                    <span class="text-slate-500 block">Customer:</span>
                    <strong class="text-white">{{ $invoice['customer_name'] ?? 'Sarah Mitchell' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block">Vehicle:</span>
                    <strong class="text-[#c6ff00]">{{ $invoice['vehicle_type'] ?? 'Luton Tail-Lift Van' }}</strong>
                </div>
                <div class="col-span-2">
                    <span class="text-slate-500 block">Pickup & Delivery Route:</span>
                    <strong class="text-white">{{ $invoice['pickup_address'] ?? 'Manchester (M1 1AE)' }} ➔ {{ $invoice['delivery_address'] ?? 'London (SW1A 1AA)' }}</strong>
                </div>
            </div>

            <!-- Price Breakdown -->
            <div class="border-t border-slate-800 pt-4 space-y-2 font-semibold">
                <div class="flex justify-between text-slate-400">
                    <span>Subtotal:</span>
                    <span>£{{ number_format($invoice['subtotal'] ?? 150.00, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>VAT (20%):</span>
                    <span>£{{ number_format($invoice['vat_amount'] ?? 30.00, 2) }}</span>
                </div>
                <div class="flex justify-between text-base font-extrabold text-white pt-2 border-t border-slate-800">
                    <span>Total Amount Due:</span>
                    <span class="text-[#c6ff00]">£{{ number_format($invoice['total_amount'] ?? 180.00, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <form action="/api/v1/payments/process" method="POST" class="no-print space-y-6">
            <input type="hidden" name="payment_token" value="{{ $invoice['payment_token'] ?? 'PAY-DEMO' }}">
            <input type="hidden" name="payment_method" value="credit_card">

            <div class="space-y-4">
                <label class="block text-xs font-bold text-slate-300">Select Payment Method</label>
                <div class="grid grid-cols-2 gap-3 text-xs font-bold">
                    <label class="flex items-center justify-center gap-2 p-3.5 rounded-xl border border-[#c6ff00] bg-[#c6ff00]/10 text-white cursor-pointer">
                        <input type="radio" name="method" checked class="accent-[#c6ff00]">
                        <span>Credit / Debit Card</span>
                    </label>
                    <label class="flex items-center justify-center gap-2 p-3.5 rounded-xl border border-slate-800 bg-slate-950 text-slate-400 cursor-pointer">
                        <input type="radio" name="method" class="accent-[#c6ff00]">
                        <span>Stripe / Apple Pay</span>
                    </label>
                </div>

                <div class="space-y-3 pt-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">Card Number</label>
                        <input type="text" placeholder="4242 •••• •••• 4242" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-[#c6ff00]">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 mb-1">Expiry Date</label>
                            <input type="text" placeholder="MM / YY" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-[#c6ff00]">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 mb-1">CVC Code</label>
                            <input type="text" placeholder="123" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-[#c6ff00]">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-4 rounded-2xl bg-[#c6ff00] hover:bg-[#b2e600] text-[#0a192f] font-extrabold text-sm transition-all shadow-lg shadow-[#c6ff00]/20 flex items-center justify-center gap-2">
                <span>Pay £{{ number_format($invoice['total_amount'] ?? 180.00, 2) }} & Confirm Booking</span>
            </button>
        </form>

        <p class="text-[10px] text-center text-slate-500">
            🔒 256-Bit SSL Encrypted Payment • Free £50,000 Goods-in-Transit Insurance Included
        </p>

    </div>
</body>
</html>
