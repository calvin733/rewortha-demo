@props(['fields' => []])

@if($errors->any())
    <div class="notice notice-error mb-5" role="alert" tabindex="-1" data-error-summary aria-labelledby="validation-title">
        <x-icon name="info" class="mt-0.5 size-5" />
        <div class="min-w-0">
            <p id="validation-title" class="font-semibold">A few details need another look.</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach($errors->getMessages() as $field => $messages)
                    @foreach($messages as $message)
                        <li>
                            @if(in_array($field, $fields, true))
                                <a href="#{{ $field }}" class="inline-flex min-h-11 items-center underline underline-offset-4 [overflow-wrap:anywhere]">{{ $message }}</a>
                            @else
                                <span>{{ $message }}</span>
                            @endif
                        </li>
                    @endforeach
                @endforeach
            </ul>
        </div>
    </div>
@endif
