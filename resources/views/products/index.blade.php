<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Stripe Checkout</title>            
        <!-- Styles -->
        <script src="https://cdn.tailwindcss.com"></script>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <style>
            .bunny-regular{
                font-family: 'figtree';
                font-weight: 400;
            }

            .bunny-semibold{
                font-family: 'figtree';
                font-weight: 600;
            }
        </style>
    </head>
    <body class="bunny-regular antialiased dark:bg-black dark:text-white/50">
        <h1 class="text-3xl font-semibold text-cyan-600 text-center font-sans my-6">STRIPE CHECKOUT</h1>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 divide-2 divide-gray-900 p-6">
            @foreach ($products as $i => $product)
                <div class="border">
                    <img src="{{$product->image}}" alt="product {{$i+1}} image" class="w-full h-48">
                    <div class="p-2">
                        <h2>{{$product->name}}</h2>
                        <h5 class="mt-2">${{$product->price}}</h5>
                    </div>
                </div>
            @endforeach
            <p>
                <form action="/create-checkout-session" method="post">
                    @csrf
                    <button type="submit" class="rounded-2xl px-4 py-2 outline-none bg-blue-600 text-white text-xs upperccase font-semibold hover:bg-opacity-90">Proceed to checkout</button>
                </form>
            </p>
        </div>
    </body>
</html>