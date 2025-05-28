<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AutoRebalance</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>
  <header>
    <div class="logo">{{ config('app.name') }}</div>
    <div class="auth-buttons">
      <button>Sign In</button>
      <button>Sign Up</button>
    </div>
  </header>

  <section class="hero">
    <h1>Automated Crypto Portfolio Rebalancing</h1>
    <p>Maintain your target allocation between BTC and XAUT with minimal effort. Let our open-source engine rebalance for you automatically.</p>
  </section>

  <section class="features" id="features">
    <div class="feature">
      <h3>Automatic Rebalancing</h3>
      <p>Set your target ratio and let our engine monitor the market and rebalance when deviations exceed your threshold.</p>
    </div>
    <div class="feature">
      <h3>USDT Bridge</h3>
      <p>Handles conversions between BTC/XAUT via USDT when direct pairs aren't available.</p>
    </div>
    <div class="feature">
      <h3>Open Source & Transparent</h3>
      <p>Auditable codebase hosted on GitHub. Community contributions welcome.</p>
    </div>
    <div class="feature">
      <h3>Secure API Integration</h3>
      <p>Connect your exchange account via secure API keys without compromising your custody.</p>
    </div>
  </section>

  <footer>
    &copy; 2025 AutoRebalance. Built with love for the crypto community.
    <a href="https://github.com" target="_blank" rel="noopener">
      <svg class="github-icon" viewBox="0 0 24 24">
        <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.387.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 17.07 3.633 16.7 3.633 16.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.834 2.809 1.304 3.495.997.108-.776.418-1.305.76-1.605-2.665-.3-5.466-1.334-5.466-5.93 0-1.31.468-2.38 1.236-3.22-.124-.303-.535-1.522.117-3.176 0 0 1.008-.322 3.3 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.29-1.552 3.296-1.23 3.296-1.23.653 1.654.242 2.873.118 3.176.77.84 1.234 1.91 1.234 3.22 0 4.61-2.804 5.625-5.475 5.921.43.37.823 1.1.823 2.222 0 1.606-.015 2.898-.015 3.293 0 .32.218.694.825.576C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
      </svg>
    </a>
  </footer>
</body>
</html>