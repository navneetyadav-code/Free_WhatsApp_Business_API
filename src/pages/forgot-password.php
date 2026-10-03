<?php

if (current_user()) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        json_response(['ok' => false, 'error' => 'Already logged in.'], 400);
    }
    redirect_to('index.php?page=dashboard');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $action = $data['action'] ?? '';

    if ($action === 'request_otp') {
        $email = mb_strtolower(trim((string) ($data['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(['ok' => false, 'error' => 'Invalid email address.'], 400);
        }

        $pdo = Database::pdo();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM password_resets WHERE email = ? AND expires_at > NOW() - INTERVAL 15 MINUTE");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() >= 3) {
            json_response(['ok' => false, 'error' => 'Too many requests. Please try again later.'], 429);
        }

        $stmt = $pdo->prepare("SELECT id, phone FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            json_response(['ok' => true]);
        }

        $userId = (int) $user['id'];
        $phone = $user['phone'] ?? '';
        $otp = sprintf('%06d', random_int(0, 999999));

        $stmt = $pdo->prepare("INSERT INTO password_resets (email, phone, otp, expires_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
        $stmt->execute([$email, $phone, $otp]);

        $viaEmail = app_config('otp.via_email');
        $viaPhone = app_config('otp.via_phone');
        $sentVia = [];

        if ($viaEmail) {
            $subject = "Your Password Reset OTP";
            $message = "Your OTP is: $otp\nIt expires in 15 minutes.";
            send_smtp_email($email, $subject, $message);
            $sentVia[] = 'email';
        }

        if ($viaPhone && $phone !== '') {
            $sysKey = app_config('system_api.key');
            $sysSecret = app_config('system_api.secret');

            if ($sysKey && $sysSecret) {
                $apiUrl = rtrim((string) app_config('app.public_url'), '/') . '/api/send.php';
                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                    'to' => $phone,
                    'message' => "Your WhatsApp API Hub password reset OTP is: *$otp*\nIt expires in 15 minutes."
                ]));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'X-API-Key: ' . $sysKey,
                    'X-API-Secret: ' . $sysSecret,
                ]);
                curl_exec($ch);
                curl_close($ch);
                $sentVia[] = 'WhatsApp';
            }
        }

        json_response(['ok' => true, 'sent_via' => $sentVia]);
    } elseif ($action === 'verify_otp') {
        $email = mb_strtolower(trim((string) ($data['email'] ?? '')));
        $otp = trim((string) ($data['otp'] ?? ''));
        $newPassword = (string) ($data['new_password'] ?? '');

        if (strlen($newPassword) < 8) {
            json_response(['ok' => false, 'error' => 'Password must be at least 8 characters.'], 400);
        }

        $pdo = Database::pdo();
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? AND otp = ? AND expires_at > NOW() ORDER BY expires_at DESC LIMIT 1");
        $stmt->execute([$email, $otp]);
        $reset = $stmt->fetch();

        if (!$reset) {
            json_response(['ok' => false, 'error' => 'Invalid or expired OTP.'], 400);
        }

        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $email]);

        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$email]);

        json_response(['ok' => true]);
    }
    
    json_response(['ok' => false, 'error' => 'Invalid action.'], 400);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Business API - Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            dark: '#042F2E',    // Deep teal for premium feel
                            wa_dark: '#075E54', // Classic WA dark green
                            wa_med: '#128C7E',  // Classic WA green
                            wa_light: '#25D366' // WA primary action green
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'float-delayed': 'float 6s ease-in-out 3s infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom scrollbar to avoid layout shifts */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Form Autofill styling to maintain clean look */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active{
            -webkit-box-shadow: 0 0 0 30px white inset !important;
            -webkit-text-fill-color: #111827 !important;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased min-h-screen flex overflow-x-hidden">

    <!-- Left Promotional Panel (Hidden on mobile, 38% width on desktop) -->
    <div class="hidden lg:flex lg:w-[38%] bg-gradient-to-br from-brand-dark via-brand-wa_dark to-brand-wa_med p-10 flex-col justify-between relative overflow-hidden shadow-2xl z-10">
        
        <!-- Subtle Floating Abstract Background Elements -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <!-- Floating Node 1 -->
            <svg class="absolute top-20 right-10 opacity-20 animate-float text-white w-32 h-32" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="20" y="20" width="60" height="60" rx="16" stroke="currentColor" stroke-width="2"/>
                <circle cx="80" cy="80" r="6" fill="currentColor"/>
                <circle cx="20" cy="20" r="4" fill="currentColor"/>
                <path d="M20 50 H80" stroke="currentColor" stroke-width="1" stroke-dasharray="4 4"/>
            </svg>
            <!-- Floating Node 2 -->
            <svg class="absolute bottom-40 -left-10 opacity-10 animate-float-delayed text-brand-wa_light w-48 h-48" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M50 10 C 25 10, 10 25, 10 50 C 10 75, 25 90, 50 90 C 75 90, 90 75, 90 50 C 90 25, 75 10, 50 10 Z" stroke="currentColor" stroke-width="2"/>
                <path d="M10 50 Q 50 20 90 50" stroke="currentColor" stroke-width="1" fill="transparent"/>
                <circle cx="50" cy="50" r="8" fill="currentColor"/>
            </svg>
        </div>

        <!-- Top Logo -->
        <div class="relative z-10 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center backdrop-blur-sm border border-white/20">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                </svg>
            </div>
            <span class="font-semibold text-white tracking-wide text-sm">WhatsApp Business API</span>
        </div>

        <!-- Middle Content -->
        <div class="relative z-10 mb-12 mt-16">
            <h1 class="text-4xl font-bold text-white leading-tight mb-5 tracking-tight">Connect.<br>Automate.<br>Grow.</h1>
            <p class="text-white/80 text-sm leading-relaxed max-w-sm mb-10">
                Unlock enterprise-grade messaging. Build scalable customer experiences with the official Meta API infrastructure.
            </p>
            
            <!-- Compact Feature Highlights -->
            <ul class="space-y-4">
                <li class="flex items-center gap-3 text-sm font-medium text-white/90">
                    <div class="w-5 h-5 rounded-full bg-brand-wa_light/20 flex items-center justify-center text-brand-wa_light border border-brand-wa_light/30">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    High-volume message delivery
                </li>
                <li class="flex items-center gap-3 text-sm font-medium text-white/90">
                    <div class="w-5 h-5 rounded-full bg-brand-wa_light/20 flex items-center justify-center text-brand-wa_light border border-brand-wa_light/30">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    Automated workflows & routing
                </li>
                <li class="flex items-center gap-3 text-sm font-medium text-white/90">
                    <div class="w-5 h-5 rounded-full bg-brand-wa_light/20 flex items-center justify-center text-brand-wa_light border border-brand-wa_light/30">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    Secure E2E encryption
                </li>
            </ul>
        </div>

        <!-- Bottom Footer -->
        <div class="relative z-10 text-xs text-white/50 font-medium">
            &copy; 2026 API Hub. Powered by Meta infrastructure.
        </div>
    </div>

    <!-- Right Recovery Form Panel (62% width on desktop, 100% on mobile) -->
    <div class="w-full lg:w-[62%] flex items-center justify-center p-6 sm:p-8 lg:p-12 relative">
        
        <!-- Decorative subtle background on right side -->
        <div class="absolute inset-0 bg-white bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:20px_20px] opacity-50 z-0"></div>

        <div class="w-full max-w-[400px] bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 p-8 sm:p-10 z-10 relative">
            
            <!-- Logo inside form (for visual balance on desktop and main branding on mobile) -->
            <div class="flex items-center gap-2 mb-8">
                <svg class="w-6 h-6 text-brand-wa_light" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                </svg>
                <span class="font-bold text-gray-900 tracking-tight lg:hidden">API Hub</span>
            </div>

            <!-- Header -->
            <div class="mb-7">
                <!-- Back Button -->
                <a href="<?= e(base_url('index.php?page=login')) ?>" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-800 transition-colors mb-4 group">
                    <svg class="w-4 h-4 mr-1.5 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to login
                </a>
                
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Forgot Password</h2>
                <p class="text-sm text-gray-500 mt-1.5 font-medium leading-relaxed">Enter the email address associated with your account, and we'll send you a link to reset your password.</p>
            </div>

            <!-- Forgot Password Form -->
            <form id="forgotPasswordForm" onsubmit="handleReset(event)" class="space-y-5">
                <div id="errorAlert" class="hidden p-3 text-sm rounded-xl bg-red-50 text-red-700"></div>
                
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Work Email</label>
                    <input type="email" id="email" 
                           class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 focus:bg-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-brand-wa_light/10 focus:border-brand-wa_light transition-all duration-200" 
                           placeholder="name@company.com" 
                           required>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" id="submitBtn" class="w-full bg-brand-wa_light hover:bg-[#1DA851] text-white font-semibold text-sm py-2.5 rounded-xl transition-all duration-200 shadow-sm shadow-brand-wa_light/20 active:scale-[0.98] flex justify-center items-center gap-2 mt-2">
                    Send Reset Link
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </form>

            
            <div id="successMessage" class="hidden text-center py-4">
                <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-brand-wa_light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 id="successHeading" class="text-lg font-bold text-gray-900 mb-2">Check your inbox</h3>
                <p class="text-sm text-gray-500 mb-6">We've sent a 6-digit OTP to <span id="displayEmail" class="font-medium text-gray-700"></span>.</p>
                
                <form id="otpForm" onsubmit="handleVerifyOTP(event)" class="space-y-4 text-left">
                    <div id="otpErrorAlert" class="hidden p-3 text-sm rounded-xl bg-red-50 text-red-700"></div>
                    <div>
                        <label for="otp" class="block text-sm font-semibold text-gray-700 mb-1.5">Enter OTP</label>
                        <input type="text" id="otp" pattern="[0-9]{6}" title="6-digit OTP" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 focus:bg-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-brand-wa_light/10 focus:border-brand-wa_light transition-all duration-200 text-center tracking-widest text-lg font-bold" placeholder="------" required>
                    </div>
                    <div>
                        <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-1.5">New Password</label>
                        <input type="password" id="new_password" minlength="8" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 focus:bg-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-brand-wa_light/10 focus:border-brand-wa_light transition-all duration-200" placeholder="••••••••" required>
                    </div>
                    <button type="submit" id="verifyBtn" class="w-full bg-brand-wa_light hover:bg-[#1DA851] text-white font-semibold text-sm py-2.5 rounded-xl transition-all duration-200 shadow-sm shadow-brand-wa_light/20 active:scale-[0.98]">
                        Reset Password
                    </button>
                </form>

                <p class="mt-6 text-sm text-gray-500">
                    Didn't receive it? <button onclick="handleReset(event, true)" id="resendBtn" class="font-semibold text-brand-wa_med hover:text-brand-wa_dark transition-colors">Click to resend</button>
                </p>
            </div>

            <!-- Footer (Contact Support option if needed) -->
            <p class="mt-8 text-center text-sm font-medium text-gray-500">
                Having trouble? 
                <a href="<?= e(base_url('index.php?page=login')) ?>" class="font-semibold text-brand-wa_med hover:text-brand-wa_dark transition-colors">Log In</a>
            </p>

        </div>
    </div>

    <script>
        
        function handleReset(e, isResend = false) {
            if(e) e.preventDefault();
            
            const emailInput = document.getElementById('email').value;
            const btn = isResend ? document.getElementById('resendBtn') : document.getElementById('submitBtn');
            const form = document.getElementById('forgotPasswordForm');
            const successDiv = document.getElementById('successMessage');
            const displayEmail = document.getElementById('displayEmail');
            const errorAlert = document.getElementById('errorAlert');
            const otpErrorAlert = document.getElementById('otpErrorAlert');
            
            const originalText = btn.innerHTML;
            
            btn.innerHTML = `<svg class="animate-spin inline h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Sending...`;
            btn.disabled = true;
            btn.classList.add('opacity-90', 'cursor-wait');

            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'request_otp', email: emailInput })
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                btn.classList.remove('opacity-90', 'cursor-wait');

                if (!data.ok) {
                    if (isResend) {
                        otpErrorAlert.textContent = data.error;
                        otpErrorAlert.classList.remove('hidden');
                    } else {
                        errorAlert.textContent = data.error;
                        errorAlert.classList.remove('hidden');
                    }
                } else {
                    displayEmail.textContent = emailInput;
                    
                    if (data.sent_via && data.sent_via.length > 0) {
                        const methods = data.sent_via.join(' and ');
                        document.getElementById('successHeading').textContent = 'Check your ' + methods;
                    }
                    
                    form.classList.add('hidden');
                    successDiv.classList.remove('hidden');
                    if (isResend) {
                        otpErrorAlert.classList.add('hidden');
                        btn.innerHTML = "Sent!";
                        setTimeout(() => { btn.innerHTML = originalText; }, 2000);
                    }
                }
            })
            .catch(err => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                btn.classList.remove('opacity-90', 'cursor-wait');
            });
        }

        function handleVerifyOTP(e) {
            e.preventDefault();
            const emailInput = document.getElementById('email').value;
            const otpInput = document.getElementById('otp').value;
            const newPasswordInput = document.getElementById('new_password').value;
            const btn = document.getElementById('verifyBtn');
            const otpErrorAlert = document.getElementById('otpErrorAlert');
            
            const originalText = btn.innerHTML;
            btn.innerHTML = "Verifying...";
            btn.disabled = true;

            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'verify_otp', email: emailInput, otp: otpInput, new_password: newPasswordInput })
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                if (!data.ok) {
                    otpErrorAlert.textContent = data.error;
                    otpErrorAlert.classList.remove('hidden');
                } else {
                    window.location.href = '<?= e(base_url("index.php?page=login")) ?>';
                }
            })
            .catch(err => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
</script>
</body>
</html>
<?php exit; ?>
