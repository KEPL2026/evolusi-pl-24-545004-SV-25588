<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SmartSplit — Student Budget Allocation</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { roboto: ['Roboto', 'sans-serif'] },
                    colors: {
                        earth: {
                            base: '#FDFBF7',
                            card: '#F7F3EB',
                            surface: '#EFE9DE',
                            blue: '#3A5A78',
                            blueHover: '#2E4861',
                            orange: '#D96B27',
                            orangeSoft: '#FBEFE8',
                            textMain: '#2D2825',
                            textMuted: '#716B64',
                            danger: '#C84630',
                            dangerBg: '#FDF0ED',
                        }
                    },
                    borderRadius: { '16': '16px' }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Roboto', sans-serif; background-color: #FDFBF7; color: #2D2825; }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between selection:bg-earth-orange selection:text-white"
      x-data="{
          isModalOpen: false,
          currentView: 'form',
          allowance: '',
          foodPct: 50,
          opsPct: 30,
          healPct: 20,
          validationError: '',
          calculatedFood: 0,
          calculatedOps: 0,
          calculatedHeal: 0,
          isLoading: false,

          get totalPct() {
              return (Number(this.foodPct) || 0) + (Number(this.opsPct) || 0) + (Number(this.healPct) || 0);
          },

          validateForm() {
              const amount = Number(this.allowance);
              if (!amount || amount < 10000) {
                  this.validationError = 'Please enter a valid monthly allowance of at least Rp 10.000.';
                  return;
              }
              if (this.totalPct !== 100) {
                  this.validationError = 'The sum of all categories must equal exactly 100%. Current total is ' + this.totalPct + '%.';
                  return;
              }
              this.validationError = '';
              this.currentView = 'confirm';
          },

          executeCalculation() {
              this.isLoading = true;
              const token = document.head.querySelector('meta[name=csrf-token]').content;
              fetch('{{ route('budget.calculate') }}', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'Accept': 'application/json',
                      'X-CSRF-TOKEN': token
                  },
                  body: JSON.stringify({
                      monthly_income: Number(this.allowance),
                      food_pct: Number(this.foodPct),
                      operational_pct: Number(this.opsPct),
                      healing_pct: Number(this.healPct)
                  })
              })
              .then(async (res) => {
                  const json = await res.json();
                  if (res.ok && json.status === 'success') {
                      this.calculatedFood = json.data.allocation.food;
                      this.calculatedOps  = json.data.allocation.operational;
                      this.calculatedHeal = json.data.allocation.healing;
                      this.currentView = 'result';
                  } else {
                      const firstError = json.errors ? Object.values(json.errors)[0][0] : json.message;
                      this.validationError = firstError || 'Terjadi kesalahan saat menghitung alokasi.';
                      this.currentView = 'form';
                  }
              })
              .catch(() => {
                  this.validationError = 'Gagal terhubung ke server. Coba lagi.';
                  this.currentView = 'form';
              })
              .finally(() => { this.isLoading = false; });
          },

          resetSystem() {
              this.allowance = '';
              this.foodPct = 50;
              this.opsPct = 30;
              this.healPct = 20;
              this.validationError = '';
              this.currentView = 'form';
              this.isModalOpen = false;
          },

          formatCurrency(value) {
              return 'Rp ' + Number(value).toLocaleString('id-ID');
          }
      }">

    <header class="w-full max-w-6xl mx-auto px-6 py-8 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-[16px] bg-earth-blue flex items-center justify-center text-white shadow-sm">
                <svg class="w-6 h-6 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a8 8 0 0 1-8 8H6a2 2 0 0 1-2-2V7"/>
                    <circle cx="18" cy="14" r="1"/>
                </svg>
            </div>
            <span class="text-xl font-bold tracking-tight text-earth-textMain">SmartSplit</span>
        </div>
        <div class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-[16px] bg-earth-card border border-earth-surface text-xs font-medium text-earth-textMuted">
            <svg class="w-4 h-4 stroke-earth-blue" fill="none" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Stateless System • No Database Storage
        </div>
    </header>

    <main class="w-full max-w-4xl mx-auto px-6 py-12 md:py-20 flex-1 flex flex-col items-center justify-center text-center">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-[16px] bg-earth-orangeSoft border border-earth-orange/20 text-earth-orange text-xs font-semibold tracking-wide uppercase mb-6">
            <svg class="w-4 h-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            Balanced Financial Planning
        </div>

        <h1 class="text-4xl sm:text-6xl font-black tracking-tight text-earth-textMain leading-tight mb-6">
            Allocate Your Allowance,<br>
            <span class="text-earth-blue">Spend Wisely Every Month.</span>
        </h1>

        <p class="text-earth-textMuted text-base sm:text-lg max-w-xl mx-auto mb-10 leading-relaxed font-normal">
            A minimalist budgeting tool designed for university students. Preset allocation ratio 50/30/20 for Food, Operations, and Lifestyle.
        </p>

        <button
            @click="isModalOpen = true; currentView = 'form'"
            class="px-8 py-4 rounded-[16px] bg-earth-orange hover:bg-[#C25D1E] text-white text-base font-medium shadow-md transition duration-200 flex items-center gap-3">
            <span>Split My Budget</span>
            <svg class="w-5 h-5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </button>
    </main>

    <footer class="w-full max-w-6xl mx-auto px-6 py-8 text-center text-xs text-earth-textMuted border-t border-earth-surface">
        Software Construction and Evolution • Universitas Gadjah Mada
    </footer>

    <div
        x-show="isModalOpen"
        x-cloak
        class="fixed inset-0 z-50 bg-[#2D2825]/40 backdrop-blur-sm flex items-center justify-center p-4"
        @keydown.escape.window="resetSystem()">

        <div
            @click.outside="resetSystem()"
            class="w-full max-w-lg bg-white rounded-[16px] border border-earth-surface shadow-xl p-6 sm:p-8 relative">

            <button
                @click="resetSystem()"
                class="absolute top-6 right-6 w-9 h-9 rounded-[16px] bg-earth-card hover:bg-earth-surface flex items-center justify-center text-earth-textMuted hover:text-earth-textMain transition">
                <svg class="w-5 h-5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <!-- 1. FORM -->
            <div x-show="currentView === 'form'">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-earth-textMain">Calculate Budget</h2>
                    <p class="text-sm text-earth-textMuted mt-1">Specify your allowance and customize your allocation ratios.</p>
                </div>

                <template x-if="validationError">
                    <div class="mb-5 p-4 rounded-[16px] bg-earth-dangerBg border border-earth-danger/30 flex items-start gap-3">
                        <svg class="w-5 h-5 stroke-earth-danger flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="text-xs font-medium text-earth-danger leading-relaxed" x-text="validationError"></div>
                    </div>
                </template>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-earth-textMuted mb-2">
                            Monthly Allowance (IDR)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-earth-textMuted font-medium text-sm">Rp</span>
                            <input
                                type="number"
                                x-model="allowance"
                                placeholder="1500000"
                                class="w-full pl-12 pr-4 py-3.5 rounded-[16px] bg-earth-card border border-earth-surface text-earth-textMain font-medium placeholder:text-earth-textMuted/50 focus:outline-none focus:border-earth-blue transition"
                            >
                        </div>
                    </div>

                    <div class="pt-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-earth-textMuted mb-2">
                            Allocation Ratios (customize as you like — must total 100%)
                        </label>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="p-3 rounded-[16px] bg-earth-card border border-earth-surface">
                                <span class="block text-xs font-medium text-earth-textMuted mb-1">Food</span>
                                <div class="flex items-center">
                                    <input type="number" x-model="foodPct" class="w-full bg-transparent text-earth-textMain font-bold text-base focus:outline-none">
                                    <span class="text-xs text-earth-textMuted font-medium">%</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-[16px] bg-earth-card border border-earth-surface">
                                <span class="block text-xs font-medium text-earth-textMuted mb-1">Operations</span>
                                <div class="flex items-center">
                                    <input type="number" x-model="opsPct" readonly
                                        class="w-full bg-transparent text-earth-textMain font-bold text-base focus:outline-none cursor-not-allowed">
                                    <span class="text-xs text-earth-textMuted font-medium">%</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-[16px] bg-earth-card border border-earth-surface">
                                <span class="block text-xs font-medium text-earth-textMuted mb-1">Lifestyle</span>
                                <div class="flex items-center">
                                    <input type="number" x-model="healPct" readonly
                                        class="w-full bg-transparent text-earth-textMain font-bold text-base focus:outline-none cursor-not-allowed">
                                    <span class="text-xs text-earth-textMuted font-medium">%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-[16px] bg-earth-base border border-earth-surface flex items-center justify-between text-xs">
                        <span class="font-medium text-earth-textMuted">Cumulative Allocation</span>
                        <span
                            class="font-bold px-2.5 py-0.5 rounded-[16px]"
                            :class="totalPct === 100 ? 'bg-earth-blue/10 text-earth-blue' : 'bg-earth-dangerBg text-earth-danger'"
                            x-text="totalPct + '% / 100%'">
                        </span>
                    </div>

                    <button
                        @click="validateForm()"
                        class="w-full mt-2 py-3.5 rounded-[16px] bg-earth-blue hover:bg-earth-blueHover text-white font-medium transition shadow-sm flex items-center justify-center gap-2">
                        <span>Review Allocation</span>
                        <svg class="w-4 h-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- 2. CONFIRM -->
            <div x-show="currentView === 'confirm'">
                <div class="text-center mb-6">
                    <div class="w-12 h-12 rounded-[16px] bg-earth-orangeSoft text-earth-orange flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-earth-textMain">Confirm Parameters</h2>
                    <p class="text-sm text-earth-textMuted mt-1">Verify your allocation parameters before calculation.</p>
                </div>

                <div class="p-4 rounded-[16px] bg-earth-card border border-earth-surface space-y-3 mb-6 text-sm">
                    <div class="flex justify-between items-center py-1 border-b border-earth-surface">
                        <span class="text-earth-textMuted">Total Allowance</span>
                        <span class="font-bold text-earth-textMain" x-text="formatCurrency(allowance)"></span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-earth-textMuted">Food Ratio</span>
                        <span class="font-medium text-earth-textMain" x-text="foodPct + '%'"></span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-earth-textMuted">Operations Ratio</span>
                        <span class="font-medium text-earth-textMain" x-text="opsPct + '%'"></span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-earth-textMuted">Lifestyle Ratio</span>
                        <span class="font-medium text-earth-textMain" x-text="healPct + '%'"></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button @click="currentView = 'form'"
                        class="py-3.5 rounded-[16px] bg-earth-card hover:bg-earth-surface text-earth-textMain font-medium transition text-sm">
                        Edit Values
                    </button>
                    <button @click="executeCalculation()" :disabled="isLoading"
                        class="py-3.5 rounded-[16px] bg-earth-orange hover:bg-[#C25D1E] text-white font-medium transition text-sm shadow-sm disabled:opacity-60">
                        <span x-text="isLoading ? 'Calculating...' : 'Confirm & Compute'"></span>
                    </button>
                </div>
            </div>

            <!-- 3. RESULT -->
            <div x-show="currentView === 'result'">
                <div class="text-center mb-6">
                    <div class="w-12 h-12 rounded-[16px] bg-earth-blue/10 text-earth-blue flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-earth-textMain">Allocation Result</h2>
                    <p class="text-sm text-earth-textMuted mt-1">Total: <span class="font-bold text-earth-blue" x-text="formatCurrency(allowance)"></span></p>
                </div>

                <div class="space-y-3 mb-6">
                    <div class="p-4 rounded-[16px] bg-earth-card border border-earth-surface flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[16px] bg-white border border-earth-surface flex items-center justify-center text-earth-blue">
                                <svg class="w-5 h-5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8zM6 1v3M10 1v3M14 1v3"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-earth-textMuted uppercase tracking-wider">Food</span>
                                <span class="text-xs font-bold text-earth-blue ml-1" x-text="'(' + foodPct + '%)'"></span>
                                <p class="text-lg font-bold text-earth-textMain" x-text="formatCurrency(calculatedFood)"></p>
                            </div>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-[16px] bg-white border border-earth-surface text-earth-textMuted font-medium">Daily Need</span>
                    </div>

                    <div class="p-4 rounded-[16px] bg-earth-card border border-earth-surface flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[16px] bg-white border border-earth-surface flex items-center justify-center text-earth-blue">
                                <svg class="w-5 h-5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-earth-textMuted uppercase tracking-wider">Operations</span>
                                <span class="text-xs font-bold text-earth-blue ml-1" x-text="'(' + opsPct + '%)'"></span>
                                <p class="text-lg font-bold text-earth-textMain" x-text="formatCurrency(calculatedOps)"></p>
                            </div>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-[16px] bg-white border border-earth-surface text-earth-textMuted font-medium">Commute</span>
                    </div>

                    <div class="p-4 rounded-[16px] bg-earth-card border border-earth-surface flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[16px] bg-white border border-earth-surface flex items-center justify-center text-earth-orange">
                                <svg class="w-5 h-5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-earth-textMuted uppercase tracking-wider">Lifestyle</span>
                                <span class="text-xs font-bold text-earth-orange ml-1" x-text="'(' + healPct + '%)'"></span>
                                <p class="text-lg font-bold text-earth-textMain" x-text="formatCurrency(calculatedHeal)"></p>
                            </div>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-[16px] bg-white border border-earth-surface text-earth-textMuted font-medium">Leisure</span>
                    </div>
                </div>

                <button @click="resetSystem()"
                    class="w-full py-3.5 rounded-[16px] bg-earth-textMain hover:bg-black text-white font-medium transition text-sm">
                    Complete and Clear Memory
                </button>
            </div>

        </div>
    </div>

</body>
</html>