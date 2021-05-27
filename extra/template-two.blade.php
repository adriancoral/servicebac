<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <style>
            body {
                font-family: 'Nunito', sans-serif;
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-2">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="ml-4 text-lg leading-7 font-semibold"><a href="https://laravel.com/docs" class="underline text-gray-900 dark:text-white">Documentation</a></div>
                            </div>

                            <div class="ml-12">
                                <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                                    <strong>{{ $name2 }}</strong> Donec dapibus malesuada odio nec efficitur. Praesent suscipit interdum congue. Mauris sagittis <strong>{{ $description2 }}</strong> sit amet diam elementum, id sagittis nulla convallis. Duis eget feugiat odio. Donec blandit leo et est fringilla, tincidunt volutpat velit maximus. Curabitur convallis sem non enim pellentesque, mattis suscipit ante pharetra. Aenean efficitur, massa at aliquet suscipit, mauris sapien condimentum nisl, sit amet porta diam risus vel odio. Nunc est eros, hendrerit et libero sollicitudin, finibus blandit tellus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent hendrerit, ipsum id laoreet mollis, mi diam auctor urna, interdum dignissim turpis lacus sit amet nisi. Maecenas vitae blandit ex. Morbi tincidunt nec felis sed aliquam. Quisque volutpat dictum erat, non consequat lacus ornare nec. .
                                </div>
                            </div>
                        </div>

                        <div class="p-6 border-t border-gray-200 dark:border-gray-700 md:border-t-0 md:border-l">
                            <div class="flex items-center">
                                <div class="ml-4 text-lg leading-7 font-semibold"><a href="https://laracasts.com" class="underline text-gray-900 dark:text-white">Laracasts</a></div>
                            </div>

                            <div class="ml-12">
                                <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                                    Quisque euismod, mauris vel <strong>{{ $type2 }}</strong> malesuada, lacus lorem finibus tortor, sed feugiat orci orci non sem. Nullam aliquet ex lacus, sit <strong>{{ $latitude2 }}</strong> ultrices mauris interdum ac. Aenean efficitur tempor odio quis tristique. Sed interdum lectus eget porta malesuada. Donec mattis varius elementum. Phasellus porttitor consequat lobortis. Morbi vitae augue sed tellus ornare laoreet. Curabitur et odio non lectus suscipit varius at ut justo. Fusce convallis nunc non odio ornare, sed varius massa vestibulum. Nulla mollis felis ut est sodales accumsan. Duis lobortis elit magna, rhoncus maximus justo <strong>{{ $account_id2 }}</strong> sed.
                                </div>
                            </div>
                        </div>

                        <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <div class="ml-4 text-lg leading-7 font-semibold"><a href="https://laravel-news.com/" class="underline text-gray-900 dark:text-white">Laravel News</a></div>
                            </div>

                            <div class="ml-12">
                                <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                                    Nunc arcu massa, <strong>{{ $tax_id2 }}</strong> at ex a, lacinia tincidunt libero. Aliquam erat volutpat. Etiam ac risus efficitur, laoreet eros at, vehicula nisl. Quisque rhoncus tristique ante, quis consectetur ipsum porta et. Morbi sit amet tincidunt justo, vitae efficitur odio. <strong>{{ $address2 }}</strong> ac suscipit tellus. Integer lorem ante, commodo id lacinia vitae, luctus ut lectus. Nulla eget orci nisi. Pellentesque finibus quam et tempus auctor. Etiam commodo sem a nulla facilisis tincidunt. Fusce tempor, tellus vel vehicula tristique, dui felis suscipit arcu, ut finibus ante nisi eu nisi.
                                </div>
                            </div>
                        </div>

                        <div class="p-6 border-t border-gray-200 dark:border-gray-700 md:border-l">
                            <div class="flex items-center">
                                <div class="ml-4 text-lg leading-7 font-semibold text-gray-900 dark:text-white">Vibrant Ecosystem</div>
                            </div>

                            <div class="ml-12">
                                <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                                    Contrary to <strong>{{ $longitude2 }}</strong> belief, Lorem Ipsum is not simply random text. It has roots in a piece of <strong>{{ $domiciliation_min_days2 }}</strong> Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical <strong>{{ $legal_name2 }}</strong>, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </body>
</html>
