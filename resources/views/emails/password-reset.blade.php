<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
    <style>
        @import url('https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css');
    </style>
</head>
<body class="bg-gray-100 py-10">
<div class="max-w-2xl mx-auto bg-white shadow-md rounded-lg overflow-hidden">
    <!-- Logo Section -->
    <div class="bg-gray-50 px-6 py-4 text-center">
        <img src="{{ $message->embed(public_path('logo.png')) }}" alt="Logo" class="mx-auto h-32">
    </div>

    <!-- Email Content -->
    <div class="px-6 py-8">
        <h1 class="text-2xl font-bold text-gray-800">
            Password Reset Request
        </h1>
        <p class="mt-4 text-gray-600">
            Hello {{ $user->first_name }} {{ $user->last_name }},
        </p>
        <p class="mt-4 text-gray-600">
            We received a request to reset the password for your account. To reset your password, click the button below:
        </p>
        <div class="mt-6 text-center">
            <a href="{{ 'http://localhost:5173/?token=' . $token }}"
               class="inline-block bg-indigo-600 text-white font-semibold py-2 px-4 rounded-md shadow hover:bg-indigo-700">
                Reset Password
            </a>
        </div>
        <p class="mt-6 text-gray-600">
            If you did not request a password reset, no further action is required. Your password will not be changed.
        </p>
        <p class="mt-6 text-sm text-gray-500">
            If you have any questions, feel free to reach out to our support team.
        </p>
    </div>

    <!-- Footer -->
    <div class="bg-gray-50 px-6 py-4 text-center">
        <p class="text-sm text-gray-500">
            Thank you for using our platform!
        </p>
    </div>
</div>
</body>
</html>
