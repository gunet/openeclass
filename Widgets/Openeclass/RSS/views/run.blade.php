    <div class="panel panel-admin card mt-3">
      <div class="card-body">
        @foreach ($feed_items as $feed_item)
          <h5 class="card-title"><a target="_new" href="{{ $feed_item['link'] }}" class="card-link">{{ $feed_item['title'] }}</a></h5>
          <h6 class="card-subtitle mb-2 text-muted">{{ $feed_item['pubDate'] }}</h6>
          {!! $feed_item['description'] !!}
          @if (!$loop->last)
            <hr>
          @endif
        @endforeach
      </div>
    </div>
