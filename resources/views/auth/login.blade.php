<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
        }
        .login-btn {
            background: linear-gradient(90deg, #22c55e 0%, #0d9488 100%);
        }
        .login-btn:hover { background: linear-gradient(90deg, #16a34a 0%, #0f766e 100%); }
    </style>
</head>
<body class="flex flex-col items-center justify-center p-4">

    <div class="text-center mb-8">
        <div class="inline-flex w-24 h-24 rounded-2xl bg-gradient-to-br from-green-400 to-teal-600 items-center justify-center shadow-2xl mb-4">
            <i class="fas fa-staff-snake text-white text-4xl"></i>
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold text-white drop-shadow-lg">Clinic Management</h1>
        <p class="text-xl text-white/90 mt-3 font-medium">Secure Login Portal</p>
    </div>

    <div class="bg-white/95 backdrop-blur rounded-2xl shadow-2xl w-full max-w-md p-8 md:p-10">
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                @foreach($errors->all() as $err)
                    <div><i class="fas fa-exclamation-circle mr-1"></i>{{ $err }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-5">
                <label class="block text-gray-800 font-semibold mb-2">Email Address</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"><i class="fas fa-at"></i></span>
                    <input type="email" name="email" value="{{ old('email', 'admin@clinicms.test') }}"
                           class="w-full pl-11 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:border-green-500 focus:outline-none text-gray-700"
                           placeholder="Enter your email" required autofocus>
                </div>
            </div>

            <div class="mb-5">
                <label class="block text-gray-800 font-semibold mb-2">Password</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" value="password"
                           class="w-full pl-11 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:border-green-500 focus:outline-none text-gray-700"
                           placeholder="Enter your password" required>
                </div>
            </div>

            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center text-gray-700 text-sm cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 mr-2 rounded accent-green-500">
                    Remember me
                </label>
                <a href="#" class="text-green-600 hover:text-green-700 text-sm font-semibold">
                    <i class="fas fa-key mr-1"></i>Forgot password?
                </a>
            </div>

            <button type="submit" class="login-btn w-full text-white font-bold py-3.5 rounded-xl shadow-lg flex items-center justify-center gap-2 text-lg">
                <i class="fas fa-lock"></i> Log In
            </button>
        </form>

        <p class="text-center text-gray-500 text-sm mt-6">
            <i class="fas fa-shield-alt text-green-500 mr-1"></i>
            Secure access to {{ config('app.company_name', 'Pharmacy Management System') }}
        </p>
    </div>

    <p class="text-white/80 text-sm mt-8">
        &copy; {{ date('Y') }} {{ config('app.company_name', 'Pharmacy Management System') }}. All rights reserved.
    </p>
</body>
</html>
