    <div class="panel">
        <div class="panel-body text-center">
            <span class="text-success">
                @foreach ($feed_items as $feed_item)
                    <li>{{ $feed_item->title }}</li>
                @endforeach
            </span>
        </div>
    </div>