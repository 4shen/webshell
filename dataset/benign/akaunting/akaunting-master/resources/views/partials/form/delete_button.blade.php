@php
    if (\Str::contains($url, ['.'])) {
        $page = explode('.', $url)[0];

        $url = route($url, $item->$id);
    } else {
        $page = explode('/', $url)[1];

        $url = url($url, $item->$id);
    }

    $text = $text ? $text : $page;

    $title = trans_choice('general.' . $text, 2);
    $type = mb_strtolower(trans_choice('general.' . $text, 1));

    // for module
    if (\Str::contains($text, ['::'])) {
        $title = trans_choice($text, 2);
        $type = mb_strtolower(trans_choice($text, 1)); 
    }

    $message = trans('general.delete_confirm', ['name' => '<strong>' . $name . '</strong>', 'type' => $type]);
@endphp

{!! Form::button('<i class="fa fa-trash-o" aria-hidden="true"></i> ' . trans('general.delete'), array(
    'type'    => 'button',
    'class'   => 'btn btn-danger btn-xs',
    'title'   => trans('general.delete'),
    '@click'  => 'confirmDelete("' . $url . '", "' . $title . '", "' . $message . '", "' . trans('general.cancel') . '", "' . trans('general.delete') . '")'
)) !!}
