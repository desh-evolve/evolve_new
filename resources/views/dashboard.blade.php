<x-app-layout :title="'Input Example'">
    <style>
        .form-group {
            margin-bottom: 10px;
        }

        label {
            margin-bottom: 0 !important;
        }

        
        /* Main Content Styles */
        .main-content {
            margin-left: 100px;
            padding: 20px;
            background: linear-gradient(135deg, #007bff, #00ff88);
            min-height: 70vh;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .welcome-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1.5s ease-in-out;
        }

        h1 {
            font-size: 48px;
            margin: 0;
            font-weight: bold;
        }

        p {
            font-size: 24px;
            margin: 10px 0 0;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <x-slot name="header">
        <h4 class="mb-sm-0">{{ __('Dashboard') }}</h4>
    </x-slot>

    <div class="main-content">
        <div class="welcome-container">
            <h1>Welcome to Evolve HRM</h1>
            <p>We're glad to have you here!</p>
        </div>
    </div>
</x-app-layout>
