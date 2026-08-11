const http = require('http');
const url = require('url');

const PORT = 8000;

const dummyQuotes = [
  {
    id: 1,
    quote_number: 'Q-88492',
    first_name: 'Sarah',
    last_name: 'Mitchell',
    email: 'sarah.m@company.co.uk',
    phone: '+44 07123 456789',
    contact_preference: 'Please Email Me',
    collection_postcode: 'M1 1AE (Manchester)',
    delivery_postcode: 'SW1A 1AA (London)',
    vehicle_type: 'Luton Tail-Lift Van',
    timescale: 'ASAP 60-Min Pickup',
    enquiry_type: 'Business',
    additional_info: '2 Heavy Euro Pallets (800kg total), fragile machinery components. Needs tail-lift unloading at ground level.',
    status: 'pending',
    default_price: '180.00'
  },
  {
    id: 2,
    quote_number: 'Q-77201',
    first_name: 'David',
    last_name: 'Miller',
    email: 'david.m@construct.co.uk',
    phone: '+44 07822 998877',
    contact_preference: 'WhatsApp',
    collection_postcode: 'B1 1BB (Birmingham)',
    delivery_postcode: 'LS1 4AP (Leeds)',
    vehicle_type: 'Small Courier Van',
    timescale: 'ASAP 60-Min Pickup',
    enquiry_type: 'Business',
    additional_info: '1 Box of architectural blueprints and site measurement gear (15kg). Urgent morning delivery.',
    status: 'pending',
    default_price: '110.00'
  },
  {
    id: 3,
    quote_number: 'Q-66104',
    first_name: 'Alexander',
    last_name: 'Wright',
    email: 'alex.w@gmail.com',
    phone: '+44 07933 112233',
    contact_preference: 'Phone Call',
    collection_postcode: 'G1 1XQ (Glasgow)',
    delivery_postcode: 'EH1 1YZ (Edinburgh)',
    vehicle_type: 'Medium Courier Van',
    timescale: 'Today Afternoon',
    enquiry_type: 'Personal',
    additional_info: '3 Crates of household antiques and artwork. Extra protective blankets required.',
    status: 'pending',
    default_price: '95.00'
  },
  {
    id: 4,
    quote_number: 'Q-55923',
    first_name: 'Claire',
    last_name: 'Bennet',
    email: 'claire@legalchambers.co.uk',
    phone: '+44 07744 556677',
    contact_preference: 'Please Email Me',
    collection_postcode: 'BS1 3AG (Bristol)',
    delivery_postcode: 'CF10 1BH (Cardiff)',
    vehicle_type: 'Courier Car',
    timescale: 'ASAP 60-Min Pickup',
    enquiry_type: 'Business',
    additional_info: 'Sealed confidential court brief bundle. Hand-to-hand named recipient signature delivery required before court close.',
    status: 'quoted',
    default_price: '85.00'
  },
  {
    id: 5,
    quote_number: 'Q-44812',
    first_name: 'Robert',
    last_name: 'Vance',
    email: 'r.vance@vancecorp.co.uk',
    phone: '+44 07555 889900',
    contact_preference: 'WhatsApp',
    collection_postcode: 'L1 8JQ (Liverpool)',
    delivery_postcode: 'NE1 1D3 (Newcastle)',
    vehicle_type: 'Large Courier Van',
    timescale: 'Scheduled Date',
    enquiry_type: 'Business',
    additional_info: '4 Oversized trade show exhibition display banners and stand gear. Delivered straight to venue floor.',
    status: 'converted',
    default_price: '220.00'
  }
];

const server = http.createServer((req, res) => {
  const parsedUrl = url.parse(req.url, true);
  const path = parsedUrl.pathname;

  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Content-Type', 'text/html; charset=utf-8');

  const getLayoutHeader = (activeTab) => `
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>InstaDrop Admin Panel — Full-Width 2-Column Inspector</title>
      <script src="https://cdn.tailwindcss.com"></script>
      <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
      <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
    </head>
    <body class="bg-[#f8fafc] text-slate-800 min-h-screen flex">
      <aside class="w-64 bg-[#0a192f] border-r border-slate-800 flex flex-col justify-between p-6 shrink-0 shadow-xl">
        <div class="space-y-8">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#0a192f] text-[#c6ff00] flex items-center justify-center font-black border border-[#c6ff00]/40 shadow-md">
              <svg class="w-5 h-5 text-[#c6ff00]" fill="currentColor" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            </div>
            <div>
              <span class="font-extrabold text-xl tracking-tight text-white font-display">Insta<span class="text-[#c6ff00]">Drop</span></span>
              <span class="block text-[9px] font-extrabold uppercase tracking-widest text-[#c6ff00]">Admin Dashboard</span>
            </div>
          </div>
          <nav class="space-y-1.5 text-xs font-semibold">
            <a href="/admin" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all ${activeTab === 'dashboard' ? 'bg-[#c6ff00] text-[#0a192f] font-extrabold shadow-md' : 'text-slate-200 hover:bg-white/10 hover:text-white'}">
              <svg class="w-4 h-4 ${activeTab === 'dashboard' ? 'text-[#0a192f]' : 'text-[#c6ff00]'}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
              <span>Dashboard Overview</span>
            </a>
            <a href="/admin/quotes" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all ${activeTab === 'quotes' ? 'bg-[#c6ff00] text-[#0a192f] font-extrabold shadow-md' : 'text-slate-200 hover:bg-white/10 hover:text-white'}">
              <svg class="w-4 h-4 ${activeTab === 'quotes' ? 'text-[#0a192f]' : 'text-[#c6ff00]'}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
              <span>Quote Requests (${dummyQuotes.length})</span>
            </a>
            <a href="/admin/orders" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all ${activeTab === 'orders' ? 'bg-[#c6ff00] text-[#0a192f] font-extrabold shadow-md' : 'text-slate-200 hover:bg-white/10 hover:text-white'}">
              <svg class="w-4 h-4 ${activeTab === 'orders' ? 'text-[#0a192f]' : 'text-[#c6ff00]'}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
              <span>Orders & Tracking</span>
            </a>
            <a href="/admin/settings" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all ${activeTab === 'settings' ? 'bg-[#c6ff00] text-[#0a192f] font-extrabold shadow-md' : 'text-slate-200 hover:bg-white/10 hover:text-white'}">
              <svg class="w-4 h-4 ${activeTab === 'settings' ? 'text-[#0a192f]' : 'text-[#c6ff00]'}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
              <span>System Settings</span>
            </a>
          </nav>
        </div>
        <div class="text-[10px] text-slate-400 border-t border-slate-800 pt-4">InstaDrop Admin v1.0 • Running Live</div>
      </aside>
      <main class="flex-1 overflow-y-auto p-8 space-y-8">
  `;

  const getLayoutFooter = () => `
      </main>
    </body>
    </html>
  `;

  // 1. Dashboard Overview
  if (path === '/admin' || path === '/admin/' || path === '/admin/dashboard') {
    res.writeHead(200);
    res.end(`
      ${getLayoutHeader('dashboard')}
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900 font-display">Dashboard Overview</h1>
          <p class="text-xs text-slate-500">Welcome to InstaDrop Dispatch Control Panel</p>
        </div>
        <a href="/pay/PAY-DEMO" target="_blank" class="px-4 py-2.5 bg-[#0a192f] text-white border border-[#0a192f] rounded-xl text-xs font-bold hover:bg-[#051329] transition-colors shadow-xs">
          🔗 Preview Customer Payment Checkout Page
        </a>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 text-xs">
        <div class="bg-white border border-slate-200/80 p-5 rounded-2xl space-y-1 shadow-xs">
          <p class="text-slate-500 font-semibold">Quotes Today</p>
          <p class="text-2xl font-black text-slate-900 font-display">14</p>
        </div>
        <div class="bg-white border border-slate-200/80 p-5 rounded-2xl space-y-1 shadow-xs">
          <p class="text-slate-500 font-semibold">Pending Quotes</p>
          <p class="text-2xl font-black text-amber-600 font-display">3</p>
        </div>
        <div class="bg-white border border-slate-200/80 p-5 rounded-2xl space-y-1 shadow-xs">
          <p class="text-slate-500 font-semibold">Unpaid Invoices</p>
          <p class="text-2xl font-black text-red-600 font-display">2</p>
        </div>
        <div class="bg-white border border-slate-200/80 p-5 rounded-2xl space-y-1 shadow-xs">
          <p class="text-slate-500 font-semibold">Active Deliveries</p>
          <p class="text-2xl font-black text-blue-600 font-display">5</p>
        </div>
        <div class="bg-white border border-slate-200/80 p-5 rounded-2xl space-y-1 shadow-xs">
          <p class="text-slate-500 font-semibold">Completed PODs</p>
          <p class="text-2xl font-black text-emerald-600 font-display">12</p>
        </div>
      </div>

      <div class="bg-white border border-slate-200/80 rounded-3xl p-6 space-y-4 shadow-xs">
        <h2 class="text-base font-extrabold text-slate-900 font-display">Recent Customer Quote Requests</h2>
        <div class="overflow-x-auto text-xs">
          <table class="w-full text-left">
            <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase font-bold border-b border-slate-200">
              <tr>
                <th class="p-3.5">Quote #</th>
                <th class="p-3.5">Customer Name</th>
                <th class="p-3.5">Route</th>
                <th class="p-3.5">Vehicle</th>
                <th class="p-3.5">Contact Preference</th>
                <th class="p-3.5">Status</th>
                <th class="p-3.5">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              ${dummyQuotes.slice(0, 3).map(q => `
                <tr class="hover:bg-slate-50 transition-colors cursor-pointer" onclick="window.location.href='/admin/quotes/${q.id}'">
                  <td class="p-3.5 font-extrabold text-[#0a192f]">${q.quote_number}</td>
                  <td class="p-3.5 font-bold text-slate-900">${q.first_name} ${q.last_name}</td>
                  <td class="p-3.5 font-semibold text-slate-800">${q.collection_postcode} ➔ ${q.delivery_postcode}</td>
                  <td class="p-3.5 font-semibold text-[#0a192f]">${q.vehicle_type}</td>
                  <td class="p-3.5 font-bold uppercase text-emerald-700">${q.contact_preference}</td>
                  <td class="p-3.5"><span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase ${q.status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'}">${q.status}</span></td>
                  <td class="p-3.5"><a href="/admin/quotes/${q.id}" class="px-3.5 py-1.5 bg-[#0a192f] text-white rounded-xl font-bold text-xs hover:bg-[#051329]">View Details ➔</a></td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      </div>
      ${getLayoutFooter()}
    `);
  }

  // 2. Quote Requests List View (/admin/quotes)
  else if (path === '/admin/quotes' || path === '/admin/quotes/') {
    res.writeHead(200);
    res.end(`
      ${getLayoutHeader('quotes')}
      <div>
        <h1 class="text-2xl font-extrabold text-slate-900 font-display">Quote Requests & Quotation Generator</h1>
        <p class="text-xs text-slate-500">Review customer requests list below. Click on any row to open its dedicated 11-field detail page & quotation generator.</p>
      </div>

      <div class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-extrabold text-slate-900 font-display">Incoming Quote Requests (${dummyQuotes.length})</h2>
          <span class="text-xs font-semibold text-slate-500">Click any row to open dedicated detail page</span>
        </div>

        <div class="overflow-x-auto text-xs">
          <table class="w-full text-left">
            <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase font-bold border-b border-slate-200">
              <tr>
                <th class="p-3.5">Quote #</th>
                <th class="p-3.5">Customer Name</th>
                <th class="p-3.5">Route</th>
                <th class="p-3.5">Vehicle</th>
                <th class="p-3.5">Contact Preference</th>
                <th class="p-3.5">Status</th>
                <th class="p-3.5">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              ${dummyQuotes.map(q => `
                <tr class="hover:bg-slate-50 transition-colors cursor-pointer" onclick="window.location.href='/admin/quotes/${q.id}'">
                  <td class="p-3.5 font-extrabold text-[#0a192f]">${q.quote_number}</td>
                  <td class="p-3.5 font-bold text-slate-900">${q.first_name} ${q.last_name}</td>
                  <td class="p-3.5 font-semibold text-slate-800">${q.collection_postcode} ➔ ${q.delivery_postcode}</td>
                  <td class="p-3.5 capitalize font-semibold text-[#0a192f]">${q.vehicle_type}</td>
                  <td class="p-3.5 font-bold uppercase text-emerald-700">${q.contact_preference}</td>
                  <td class="p-3.5"><span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase ${q.status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'}">${q.status}</span></td>
                  <td class="p-3.5">
                    <a href="/admin/quotes/${q.id}" class="px-3.5 py-1.5 bg-[#0a192f] text-white rounded-xl font-bold text-xs hover:bg-[#051329] transition-colors inline-flex items-center gap-1.5 shadow-xs">
                      <span>View Details & Price</span>
                      <span>➔</span>
                    </a>
                  </td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      </div>
      ${getLayoutFooter()}
    `);
  }

  // 3. FULL-WIDTH 2-COLUMN SINGLE QUOTE DETAIL PAGE (/admin/quotes/:id)
  else if (path.startsWith('/admin/quotes/')) {
    const parts = path.split('/');
    const quoteId = parseInt(parts[parts.length - 1] || '1', 10);
    const activeQuote = dummyQuotes.find(q => q.id === quoteId) || dummyQuotes[0];

    res.writeHead(200);
    res.end(`
      ${getLayoutHeader('quotes')}
      <div class="space-y-6 w-full">
        <!-- Top Breadcrumb Navigation -->
        <div class="flex items-center justify-between border-b border-slate-200/80 pb-4">
          <a href="/admin/quotes" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-xs">
            ← Back to All Quote Requests
          </a>
          <div class="flex items-center gap-3">
            <span class="text-xs font-black text-[#0a192f] bg-[#c6ff00] px-3.5 py-1.5 rounded-full shadow-xs">QUOTE #${activeQuote.quote_number}</span>
            <span class="px-3.5 py-1.5 rounded-full text-xs font-extrabold uppercase ${activeQuote.status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'}">${activeQuote.status}</span>
          </div>
        </div>

        <!-- FULL-WIDTH 2-COLUMN GRID (0% EMPTY SPACE) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          
          <!-- LEFT COLUMN (2/3 Width): Customer Profile, Route Timeline & 11-Field Grid -->
          <div class="lg:col-span-2 space-y-6">
            
            <!-- Customer Banner Card -->
            <div class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 space-y-4 shadow-sm">
              <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                  <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Customer Submission Profile</span>
                  <h1 class="text-2xl font-extrabold text-slate-900 font-display mt-0.5">${activeQuote.first_name} ${activeQuote.last_name}</h1>
                </div>
                <div>
                  <span class="text-slate-400 text-xs block text-right font-medium">Preferred Contact:</span>
                  <strong class="text-emerald-700 bg-emerald-50 px-3 py-1 rounded-md uppercase font-bold text-xs border border-emerald-200">${activeQuote.contact_preference}</strong>
                </div>
              </div>

              <!-- Pickup & Dropoff Route Graphic -->
              <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/60 flex items-center justify-between gap-4 text-xs">
                <div class="space-y-1">
                  <span class="text-slate-400 font-semibold block">Collection Postcode</span>
                  <strong class="text-slate-900 font-extrabold text-sm block">${activeQuote.collection_postcode}</strong>
                </div>
                <div class="flex-1 flex items-center justify-center px-4">
                  <div class="w-full h-0.5 bg-slate-300 relative flex items-center justify-center">
                    <span class="bg-[#0a192f] text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase">
                      ${activeQuote.vehicle_type}
                    </span>
                  </div>
                </div>
                <div class="space-y-1 text-right">
                  <span class="text-slate-400 font-semibold block">Delivery Postcode</span>
                  <strong class="text-slate-900 font-extrabold text-sm block">${activeQuote.delivery_postcode}</strong>
                </div>
              </div>
            </div>

            <!-- ALL 11 FORM FIELDS DETAILED BREAKDOWN -->
            <div class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 space-y-4 shadow-sm">
              <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3">Frontend Form Fields Breakdown (11 Inputs)</h3>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                  <span class="text-slate-400 block font-semibold">1. First Name:</span>
                  <strong class="text-slate-900 font-bold text-sm">${activeQuote.first_name}</strong>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                  <span class="text-slate-400 block font-semibold">2. Last Name:</span>
                  <strong class="text-slate-900 font-bold text-sm">${activeQuote.last_name}</strong>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                  <span class="text-slate-400 block font-semibold">3. Email Address:</span>
                  <strong class="text-slate-900 font-bold">${activeQuote.email}</strong>
                </div>

                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                  <span class="text-slate-400 block font-semibold">4. Phone Number:</span>
                  <strong class="text-slate-900 font-bold">${activeQuote.phone}</strong>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                  <span class="text-slate-400 block font-semibold">5. Contact Preference:</span>
                  <strong class="text-emerald-700 font-bold uppercase">${activeQuote.contact_preference}</strong>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                  <span class="text-slate-400 block font-semibold">6. Collection Postcode:</span>
                  <strong class="text-slate-900 font-bold text-sm">${activeQuote.collection_postcode}</strong>
                </div>

                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                  <span class="text-slate-400 block font-semibold">7. Delivery Postcode:</span>
                  <strong class="text-slate-900 font-bold text-sm">${activeQuote.delivery_postcode}</strong>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                  <span class="text-slate-400 block font-semibold">8. Vehicle Selected:</span>
                  <strong class="text-[#0a192f] font-extrabold">${activeQuote.vehicle_type}</strong>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                  <span class="text-slate-400 block font-semibold">9. Timescales:</span>
                  <strong class="text-amber-700 font-bold">${activeQuote.timescale}</strong>
                </div>

                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60 md:col-span-3">
                  <span class="text-slate-400 block font-semibold">10. Type of Enquiry:</span>
                  <strong class="text-blue-700 font-bold capitalize">${activeQuote.enquiry_type}</strong>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/60 md:col-span-3 space-y-1">
                  <span class="text-slate-400 block font-semibold">11. Any Other Information / Parcel Specs:</span>
                  <p class="text-slate-800 italic font-normal text-xs leading-relaxed">${activeQuote.additional_info}</p>
                </div>
              </div>
            </div>

          </div>

          <!-- RIGHT COLUMN (1/3 Width): Sticky Quotation Pricing Generator -->
          <div class="space-y-6">
            
            <form action="/pay/PAY-DEMO" method="GET" target="_blank" class="bg-white border-2 border-[#0a192f] rounded-3xl p-6 space-y-6 shadow-md sticky top-6">
              <div class="border-b border-slate-100 pb-3">
                <span class="text-[10px] font-black text-[#0a192f] bg-[#c6ff00] px-3 py-1 rounded-full uppercase tracking-wider">DISPATCH PRICING TOOL</span>
                <h3 class="text-base font-extrabold text-slate-900 font-display mt-2">Quotation Generator</h3>
                <p class="text-xs text-slate-500">Enter final selling price & dispatch invoice to customer.</p>
              </div>

              <div class="space-y-4 text-xs">
                <div>
                  <label class="block text-slate-700 font-bold mb-1">Full Pickup Address</label>
                  <input type="text" value="Unit 4 Logistics Park, ${activeQuote.collection_postcode}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900">
                </div>

                <div>
                  <label class="block text-slate-700 font-bold mb-1">Full Delivery Address</label>
                  <input type="text" value="Building 12 Commerce Center, ${activeQuote.delivery_postcode}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900">
                </div>

                <div>
                  <label class="block text-slate-700 font-bold mb-1">Selling Price (£ Excl. VAT)</label>
                  <input type="text" value="${activeQuote.default_price}" class="w-full bg-slate-50 border-2 border-[#0a192f] rounded-xl px-3.5 py-3 text-slate-900 font-bold text-sm">
                </div>

                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-1 text-slate-600 font-medium">
                  <div class="flex justify-between"><span>Standard VAT (20%):</span><span>Auto-Calculated</span></div>
                  <div class="flex justify-between font-bold text-slate-900"><span>Status:</span><span class="text-amber-600 font-bold">Pending Payment</span></div>
                </div>
              </div>

              <button type="submit" class="w-full py-4 rounded-2xl bg-[#0a192f] hover:bg-[#051329] text-white font-extrabold text-xs transition-colors shadow-md flex items-center justify-center gap-2">
                <span>⚡ Send Payment Link to ${activeQuote.first_name}</span>
              </button>
            </form>

          </div>

        </div>
      </div>
      ${getLayoutFooter()}
    `);
  }

  // 4. Orders & Tracking
  else if (path === '/admin/orders') {
    res.writeHead(200);
    res.end(`
      ${getLayoutHeader('orders')}
      <div>
        <h1 class="text-2xl font-extrabold text-slate-900 font-display">Orders & Delivery Status Control</h1>
        <p class="text-xs text-slate-500">Manage live active deliveries, update status, and upload POD photo/signature</p>
      </div>

      <div class="bg-white border border-slate-200/80 rounded-3xl p-6 space-y-4 shadow-xs">
        <div class="overflow-x-auto text-xs">
          <table class="w-full text-left">
            <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase font-bold border-b border-slate-200">
              <tr>
                <th class="p-3.5">Tracking #</th>
                <th class="p-3.5">Customer Name</th>
                <th class="p-3.5">Vehicle</th>
                <th class="p-3.5">Price Paid</th>
                <th class="p-3.5">Delivery Status</th>
                <th class="p-3.5">Update Status</th>
                <th class="p-3.5">POD Document</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-3.5 font-extrabold text-[#0a192f]">INSTA-884920</td>
                <td class="p-3.5 font-bold text-slate-900">Sarah Mitchell</td>
                <td class="p-3.5">Luton Tail-Lift Van</td>
                <td class="p-3.5 font-bold text-slate-900">£180.00 (Paid)</td>
                <td class="p-3.5"><span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-blue-100 text-blue-800">In Transit</span></td>
                <td class="p-3.5">
                  <select class="bg-slate-50 border border-slate-300 rounded-lg px-2 py-1 text-xs text-slate-800 font-semibold">
                    <option value="dispatched">Dispatched</option>
                    <option value="collected">Collected</option>
                    <option value="in_transit" selected>In Transit</option>
                    <option value="delivered">Delivered</option>
                  </select>
                </td>
                <td class="p-3.5"><a href="/pay/PAY-DEMO" class="px-3 py-1 bg-[#0a192f] text-white rounded-lg font-bold text-xs hover:bg-[#051329]">+ Upload POD</a></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      ${getLayoutFooter()}
    `);
  }

  // 5. System Settings
  else if (path === '/admin/settings') {
    res.writeHead(200);
    res.end(`
      ${getLayoutHeader('settings')}
      <div>
        <h1 class="text-2xl font-extrabold text-slate-900 font-display">System Settings & Hotline Configuration</h1>
        <p class="text-xs text-slate-500">Configure Business WhatsApp Number, Email Notifications, and API Keys</p>
      </div>

      <div class="max-w-3xl bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 space-y-6 text-xs shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div><label class="block font-bold text-slate-700 mb-1">Business Name</label><input type="text" value="InstaDrop Courier Services Ltd" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900"></div>
          <div><label class="block font-bold text-slate-700 mb-1">Admin Business WhatsApp Number</label><input type="text" value="+448001234455" class="w-full bg-slate-50 border border-[#0a192f] rounded-xl px-4 py-3 text-[#0a192f] font-bold"></div>
          <div><label class="block font-bold text-slate-700 mb-1">Admin Notification Email</label><input type="email" value="dispatch@instadrop.co.uk" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900"></div>
          <div><label class="block font-bold text-slate-700 mb-1">VAT Rate (%)</label><input type="text" value="20.00" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900"></div>
        </div>
        <div class="pt-2"><button class="px-6 py-3 bg-[#0a192f] text-white rounded-xl font-bold">Save System Settings</button></div>
      </div>
      ${getLayoutFooter()}
    `);
  }

  // 6. Payment Checkout Page
  else if (path.startsWith('/pay/')) {
    res.writeHead(200);
    res.end(`
      <!DOCTYPE html>
      <html lang="en">
      <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Complete Delivery Payment — InstaDrop Courier</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
        <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
      </head>
      <body class="bg-[#0a192f] text-slate-100 min-h-screen flex items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-xl bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl space-y-8">
          <div class="flex items-center justify-between border-b border-slate-800 pb-6">
            <div>
              <span class="text-xs font-bold uppercase tracking-wider text-[#c6ff00] bg-[#c6ff00]/10 px-3 py-1 rounded-full border border-[#c6ff00]/30">INSTADROP SECURE PAYMENT</span>
              <h1 class="text-2xl sm:text-3xl font-extrabold text-white font-display mt-2">Invoice Checkout</h1>
            </div>
            <div class="text-right text-xs text-slate-400">
              <p>Invoice #: <strong class="text-white">INV-2026-8801</strong></p>
              <p>Status: <strong class="text-amber-400 font-bold uppercase">UNPAID</strong></p>
            </div>
          </div>

          <div class="bg-slate-950/70 rounded-2xl p-6 border border-slate-800 space-y-4 text-xs">
            <h3 class="text-sm font-bold text-white border-b border-slate-800 pb-2">Delivery Summary</h3>
            <div class="grid grid-cols-2 gap-4 text-slate-300">
              <div><span class="text-slate-500 block">Customer:</span><strong class="text-white">Sarah Mitchell</strong></div>
              <div><span class="text-slate-500 block">Vehicle:</span><strong class="text-[#c6ff00]">Luton Tail-Lift Van</strong></div>
              <div class="col-span-2"><span class="text-slate-500 block">Pickup & Delivery Route:</span><strong class="text-white">M1 1AE (Manchester) ➔ SW1A 1AA (London)</strong></div>
            </div>
            <div class="border-t border-slate-800 pt-4 space-y-2 font-semibold">
              <div class="flex justify-between text-slate-400"><span>Subtotal:</span><span>£150.00</span></div>
              <div class="flex justify-between text-slate-400"><span>VAT (20%):</span><span>£30.00</span></div>
              <div class="flex justify-between text-base font-extrabold text-white pt-2 border-t border-slate-800"><span>Total Amount Due:</span><span class="text-[#c6ff00]">£180.00</span></div>
            </div>
          </div>

          <form onsubmit="alert('Payment Successful! Confirmation emailed and dispatched to customer WhatsApp.'); return false;" class="space-y-6">
            <div class="space-y-4">
              <label class="block text-xs font-bold text-slate-300">Select Payment Method</label>
              <div class="grid grid-cols-2 gap-3 text-xs font-bold">
                <label class="flex items-center justify-center gap-2 p-3.5 rounded-xl border border-[#c6ff00] bg-[#c6ff00]/10 text-white cursor-pointer">
                  <input type="radio" checked class="accent-[#c6ff00]"><span>Credit / Debit Card</span>
                </label>
                <label class="flex items-center justify-center gap-2 p-3.5 rounded-xl border border-slate-800 bg-slate-950 text-slate-400 cursor-pointer">
                  <input type="radio" class="accent-[#c6ff00]"><span>Stripe / Apple Pay</span>
                </label>
              </div>
              <div class="space-y-3 pt-2">
                <div><label class="block text-[11px] font-bold text-slate-400 mb-1">Card Number</label><input type="text" value="4242 •••• •••• 4242" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white"></div>
                <div class="grid grid-cols-2 gap-3">
                  <div><label class="block text-[11px] font-bold text-slate-400 mb-1">Expiry Date</label><input type="text" value="12 / 28" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white"></div>
                  <div><label class="block text-[11px] font-bold text-slate-400 mb-1">CVC Code</label><input type="text" value="882" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white"></div>
                </div>
              </div>
            </div>
            <button type="submit" class="w-full py-4 rounded-2xl bg-[#c6ff00] hover:bg-[#b2e600] text-[#0a192f] font-extrabold text-sm transition-all shadow-lg">Pay £180.00 & Confirm Booking</button>
          </form>
        </div>
      </body>
      </html>
    `);
  }

  else {
    res.writeHead(302, { Location: '/admin' });
    res.end();
  }
});

server.listen(PORT, () => {
  console.log(`InstaDrop Full-Width 2-Column Admin Inspector running on http://localhost:${PORT}`);
});
