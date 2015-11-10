    <div class="panel">
        <div class="panel-body text-center">
            <span class="text-success">
                @foreach ($feed_items as $feed_item)
                <li><a target="_new" href="{{ $feed_item[link] }}">{{ $feed_item[title] }}</a> ({{ $feed_item[pubDate] }})</li>
                {{ $feed_item[description] }}
                @endforeach
            </span>
        </div>
    </div>