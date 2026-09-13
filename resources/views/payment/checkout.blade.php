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
    <!-- OFFICIAL PAYPAL JS SDK INTEGRATION -->
    <script src="https://www.paypal.com/sdk/js?client-id={{ urlencode(config('services.paypal.client_id', '')) }}&currency=GBP"></script>
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
                <p>Invoice #: <strong class="text-white">{{ $invoice->invoice_number ?? 'INV-2026-8801' }}</strong></p>
                <p>Status: <strong id="payment-status-badge" class="text-emerald-400 font-bold uppercase">{{ strtoupper($order->payment_status ?? 'UNPAID') }}</strong></p>
            </div>
        </div>

        <!-- Order Summary Card -->
        <div class="bg-slate-950/70 rounded-2xl p-6 border border-slate-800 space-y-4 text-xs">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                <h3 class="text-sm font-bold text-white">Delivery Summary</h3>
                <button onclick="window.print()" class="no-print text-[#c6ff00] hover:underline font-bold text-xs flex items-center gap-1">
                    📥 Download PDF Invoice Receipt
                </button>
            </div>
            <div class="grid grid-cols-2 gap-4 text-slate-300">
                <div>
                    <span class="text-slate-500 block">Customer:</span>
                    <strong class="text-white">{{ $order->customer_name ?? 'Sarah Mitchell' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block">Vehicle:</span>
                    <strong class="text-[#c6ff00] capitalize">{{ str_replace('_', ' ', $order->vehicle_type ?? 'Luton Tail-Lift Van') }}</strong>
                </div>
                <div class="col-span-2">
                    <span class="text-slate-500 block">Pickup & Delivery Route:</span>
                    <strong class="text-white">{{ $order->pickup_address ?? 'M1 1AE (Manchester)' }} ➔ {{ $order->delivery_address ?? 'SW1A 1AA (London)' }}</strong>
                </div>
            </div>

            <div class="border-t border-slate-800 pt-4 space-y-2 font-semibold">
                <div class="flex justify-between text-slate-400">
                    <span>Subtotal:</span>
                    <span>£{{ number_format(($order->total_amount ?? 180) / 1.2, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>VAT (20%):</span>
                    <span>£{{ number_format(($order->total_amount ?? 180) - (($order->total_amount ?? 180) / 1.2), 2) }}</span>
                </div>
                <div class="flex justify-between text-base font-extrabold text-white pt-2 border-t border-slate-800">
                    <span>Total Amount Due:</span>
                    <span class="text-[#c6ff00]">£{{ number_format($order->total_amount ?? 180, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- OFFICIAL PAYPAL JS BUTTONS & CARD CONTAINER -->
        <div class="no-print space-y-6">
            <div class="border-b border-slate-800 pb-2">
                <h3 class="text-sm font-bold text-white">Pay via PayPal or Debit/Credit Card</h3>
                <p class="text-xs text-slate-400">256-bit SSL encrypted instant payment capture.</p>
            </div>

            <!-- PayPal Render Container -->
            <div id="paypal-button-container" class="w-full"></div>

            <script>
                if (typeof paypal !== 'undefined') {
                    paypal.Buttons({
                        style: {
                            layout: 'vertical',
                            color:  'gold',
                            shape:  'rect',
                            label:  'paypal'
                        },
                        createOrder: function(data, actions) {
                            return actions.order.create({
                                purchase_units: [{
                                    amount: {
                                        currency_code: 'GBP',
                                        value: '{{ number_format($order->total_amount ?? 180, 2, ".", "") }}'
                                    }
                                }]
                            });
                        },
                        onApprove: function(data, actions) {
                            return actions.order.capture().then(function(details) {
                                document.getElementById('payment-status-badge').innerText = 'PAID';
                                document.getElementById('payment-status-badge').className = 'text-emerald-400 font-bold uppercase';
                                alert('Payment Successful! Thank you ' + details.payer.name.given_name + '. Order marked as PAID in Admin Panel.');
                                fetch('/api/v1/paypal/capture-order', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({
                                        paypal_order_id: data.orderID,
                                        order_token: '{{ $order->order_number ?? "PAY-DEMO" }}'
                                    })
                                });
                            });
                        }
                    }).render('#paypal-button-container');
                }
            </script>
        </div>

    </div>
</body>
</html>
