                    @if (! empty($application['missing_packages']))
                        <x-minios.missing-dependencies :app="$application" :missing="$application['missing_packages']" :id="$id" />
                    @elseif (! empty($application['component']))
                        @if ((class_exists($application['component']) && is_subclass_of($application['component'], \Livewire\Component::class)) || str_starts_with($application['component'], 'livewire:') || str_starts_with($application['component'], 'pages::'))
                            @php
                                $livewireComp = str_starts_with($application['component'], 'livewire:')
                                    ? substr($application['component'], 9)
                                    : $application['component'];
                            @endphp
                            @if ($id === 'katalog')
                                <livewire:dynamic-component :is="$livewireComp" :wire:key="'minios-app-'.$id" :path="$desktopPath" />
                            @else
                                <livewire:dynamic-component :is="$livewireComp" :wire:key="'minios-app-'.$id" />
                            @endif
                        @else
                            <x-dynamic-component :component="$application['component']" />
                        @endif
                    @else
                        <div
                            class="
                                flex
                                h-full
                                min-h-72

                                flex-col
                                items-center
                                justify-center

                                gap-5

                                bg-[#f6f5f4]

                                p-10

                                text-center
                            "
                        >

                            <x-minios.icon
                                :name="$application['icon']"
                                class="size-20"
                            />


                            <div>

                                <h2
                                    class="
                                        text-xl
                                        font-semibold
                                        text-neutral-800
                                    "
                                >
                                    {{ $application['name'] }}
                                </h2>


                                <p
                                    class="
                                        mt-1

                                        text-sm
                                        text-neutral-500
                                    "
                                >
                                    {{ __('Application Host placeholder.') }}
                                </p>

                            </div>


                            <div
                                class="
                                    rounded-lg

                                    border
                                    border-neutral-200

                                    bg-white

                                    px-4
                                    py-2

                                    font-mono
                                    text-xs
                                    text-neutral-500

                                    shadow-sm
                                "
                            >
                                <span
                                    x-text="
                                        getWindow(@js($id))?.url
                                        ?? @js($application['entry'])
                                    "
                                ></span>
                            </div>

                        </div>
                    @endif
