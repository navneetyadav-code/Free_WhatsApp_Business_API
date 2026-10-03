<?php

if (current_user()) {
    redirect_to('index.php?page=dashboard');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    // If the user submits just 10 digits (without JS), assume default country
    $defaultCountryPrefix = config_env('DEFAULT_COUNTRY_CODE', 'in') === 'in' ? '+91' : '';
    if ($defaultCountryPrefix && preg_match('/^[0-9]{10}$/', $phone)) {
        $phone = $defaultCountryPrefix . $phone;
    }

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Enter a valid name, email, and password with at least 8 characters.';
    } elseif (!preg_match('/^\+[1-9]\d{6,14}$/', $phone)) {
        $error = 'Enter a valid international phone number.';
    } else {
        try {
            register_user($name, $email, $phone, $password);
            flash('success', 'Account created successfully.');
            redirect_to('index.php?page=dashboard');
        } catch (Throwable $exception) {
            $error = 'That email or phone is already registered.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Business API - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/css/intlTelInput.css">`r`n    <style>.iti { width: 100%; }</style>
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

    <!-- Right Login Form Panel (62% width on desktop, 100% on mobile) -->
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
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Create an Account</h2>
                <p class="text-sm text-gray-500 mt-1.5 font-medium">Start building your WhatsApp API integration.</p>
            </div>

            <!-- Register Form -->
            <form method="post" onsubmit="handleRegister(event)" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <?php foreach (flashes() as $flash): ?>
                    <div class="p-3 text-sm rounded-xl <?= $flash['type'] === 'error' || $flash['type'] === 'danger' ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' ?>">
                        <?= e($flash['message']) ?>
                    </div>
                <?php endforeach; ?>
                <?php if ($error): ?>
                    <div class="p-3 text-sm rounded-xl bg-red-50 text-red-700">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>
                
                <!-- Full Name Field -->
                <div>
                    <label for="fullName" class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name</label>
                    <input type="text" id="fullName" name="name" 
                           class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 focus:bg-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-brand-wa_light/10 focus:border-brand-wa_light transition-all duration-200" 
                           placeholder="Navneet Yadav" 
                           required>
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Work Email</label>
                    <input type="email" id="email" name="email" 
                           class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 focus:bg-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-brand-wa_light/10 focus:border-brand-wa_light transition-all duration-200" 
                           placeholder="name@company.com" 
                           required>
                </div>

                <!-- Phone Field -->
                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1.5">Phone Number</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 text-sm text-gray-700 bg-gray-100 border border-r-0 border-gray-200 rounded-l-xl">
                            <svg class="w-4 h-3 mr-1.5 border border-gray-200 shadow-sm" viewBox="0 0 225 150">
                                <rect width="225" height="150" fill="#f93"/>
                                <rect width="225" height="100" y="50" fill="#fff"/>
                                <rect width="225" height="50" y="100" fill="#128807"/>
                                <circle cx="112.5" cy="75" r="20" fill="none" stroke="#000088" stroke-width="3"/>
                            </svg>
                            +91
                        </span>
                        <input type="tel" id="phone" name="phone" 
                               class="w-full px-3.5 py-2.5 text-sm rounded-none rounded-r-xl border border-gray-200 bg-gray-50 focus:bg-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-brand-wa_light/10 focus:border-brand-wa_light transition-all duration-200" 
                               placeholder="9876543210" 
                               pattern="^[0-9]{10}$"
                               title="Enter a 10 digit phone number"
                               required>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                        <div class="relative flex items-center">
                            <input type="password" id="password" name="password" 
                                   class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 focus:bg-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-brand-wa_light/10 focus:border-brand-wa_light transition-all duration-200 pr-10" 
                                   placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" 
                                   required>
                            <!-- Toggle Password Button -->
                            <button type="button" onclick="togglePassword('password', 'eye-icon')" class="absolute right-3 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none flex items-center justify-center p-1" aria-label="Toggle password visibility">
                                <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password Field -->
                    <div>
                        <label for="confirmPassword" class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm Password</label>
                        <div class="relative flex items-center">
                            <input type="password" id="confirmPassword" name="confirmPassword" 
                                   class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 focus:bg-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-brand-wa_light/10 focus:border-brand-wa_light transition-all duration-200 pr-10" 
                                   placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" 
                                   required>
                            <!-- Toggle Confirm Password Button -->
                            <button type="button" onclick="togglePassword('confirmPassword', 'eye-icon-confirm')" class="absolute right-3 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none flex items-center justify-center p-1" aria-label="Toggle confirm password visibility">
                                <svg id="eye-icon-confirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="pt-2 pb-2">
                    <label class="flex items-start gap-2 cursor-pointer group">
                        <input type="checkbox" required class="mt-0.5 w-4 h-4 rounded border-gray-300 text-brand-wa_light focus:ring-brand-wa_light focus:ring-offset-0 transition-colors cursor-pointer">
                        <span class="text-xs font-medium text-gray-600 group-hover:text-gray-800 transition-colors leading-tight">
                            I agree to the <a href="#" class="text-brand-wa_med hover:text-brand-wa_dark transition-colors">Terms of Service</a> and <a href="#" class="text-brand-wa_med hover:text-brand-wa_dark transition-colors">Privacy Policy</a>.
                        </span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-brand-wa_light hover:bg-[#1DA851] text-white font-semibold text-sm py-2.5 rounded-xl transition-all duration-200 shadow-sm shadow-brand-wa_light/20 active:scale-[0.98] flex justify-center items-center gap-2 mt-2">
                    Create Account
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </form>

            <!-- OR Divider -->
            <div class="my-5 flex items-center">
                <div class="flex-grow border-t border-gray-100"></div>
                <span class="mx-4 text-xs font-semibold text-gray-400 uppercase tracking-widest">Or</span>
                <div class="flex-grow border-t border-gray-100"></div>
            </div>

            <!-- Google Signup Button -->
            <a href="<?= e(base_url('index.php?page=google-auth')) ?>" class="w-full flex items-center justify-center gap-3 bg-white border border-gray-200 hover:bg-gray-50 hover:border-gray-300 text-gray-700 font-semibold text-sm py-2.5 rounded-xl transition-all duration-200 active:scale-[0.98]">
                <svg class="w-4 h-4" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Sign up with Google
            </a>

            <!-- Footer -->
            <p class="mt-6 text-center text-sm font-medium text-gray-500">
                Already have an account? 
                <a href="<?= e(base_url('index.php?page=login')) ?>" class="font-semibold text-brand-wa_med hover:text-brand-wa_dark transition-colors">Log in</a>
            </p>

        </div>
    </div>

    <script>
        // Password Visibility Toggle Logic
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                // Eye-off icon path
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                `;
            } else {
                passwordInput.type = 'password';
                // Standard eye icon path
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        }

        // Mock Register Handler
                function handleRegister(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                console.error('Passwords do not match');
                const confirmContainer = document.getElementById('confirmPassword');
                confirmContainer.classList.remove('focus:border-brand-wa_light', 'border-gray-200');
                confirmContainer.classList.add('border-red-500', 'focus:border-red-500');
                setTimeout(() => {
                    confirmContainer.classList.add('focus:border-brand-wa_light', 'border-gray-200');
                    confirmContainer.classList.remove('border-red-500', 'focus:border-red-500');
                }, 2000);
                return;
            }

            const btn = e.target.querySelector('button[type="submit"]');
            btn.innerHTML = `<svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Creating Account...`;
            btn.classList.add('opacity-90', 'cursor-wait');
        }

    </script>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/intlTelInput.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const phoneInput = document.querySelector('input[name="phone"]');
            if (phoneInput && window.intlTelInput) {
                const iti = window.intlTelInput(phoneInput, {
                    initialCountry: "<?= e(config_env('DEFAULT_COUNTRY_CODE', 'in')) ?>", 
                    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/utils.js",
                    separateDialCode: true,
                    nationalMode: true
                });

                const form = phoneInput.closest('form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        if (phoneInput.value.trim() !== '') {
                            if (!iti.isValidNumber()) {
                                e.preventDefault();
                                alert("Please enter a valid phone number.");
                                phoneInput.focus();
                                return false;
                            }
                            // Replace input value with full E164 before submitting
                            phoneInput.value = iti.getNumber();
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>
<?php exit; ?>

