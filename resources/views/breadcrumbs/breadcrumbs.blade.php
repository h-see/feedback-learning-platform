@unless ($breadcrumbs->isEmpty())
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb p-2 ps-5">
            @foreach ($breadcrumbs as $breadcrumb)
                @if (!is_null($breadcrumb->url) && !$loop->last)
                    <li class="breadcrumb-item"><a href="{{ $breadcrumb->url }}">
                            <fa-icon :icon="['fas', 'house']"/>
                        </a></li>
                @else
                    @if(!Request::is('course/*') && !Request::is('courses/*') || (Request::is('*/tasks') | Request::is('*/exercises') | (Request::is('*/tasks-with-feedback')) | (Request::is('*/peer-reviews')) ))
                    <li class="breadcrumb-item"><a class="breadcrumb-item active" href="{{ url('/course/' . session('current_course.id'))}}">
                            {{ session('current_course.name') }}</a></li>
                    @endif
                    <li class="breadcrumb-item">{{ $breadcrumb->title }}</li>
                @endif
            @endforeach
        </ol>
    </nav>
@endunless
