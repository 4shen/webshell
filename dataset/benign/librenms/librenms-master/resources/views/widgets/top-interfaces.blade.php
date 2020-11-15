<h4>Top {{ $interface_count }} interfaces polled within {{ $time_interval }} minutes</h4>
<div class="table-responsive">
    <table class="table table-hover table-condensed table-striped bootgrid-table">
        <thead>
            <tr>
                <th class="text-left">@lang('Device')</th>
                <th class="text-left">@lang('Interface')</th>
                <th class="text-left">@lang('Total traffic')</th>
            </tr>
        </thead>
        <tbody>
        @foreach($ports as $port)
            <tr>
                <td class="text-left">@deviceLink($port->device, $port->device->shortDisplayName())</td>
                <td class="text-left">@portLink($port, $port->getShortLabel())</td>
                <td class="text-left">@portLink($port, \LibreNMS\Util\Url::portThumbnail($port))</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
