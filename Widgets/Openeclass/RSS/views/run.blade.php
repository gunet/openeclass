    <div class="col-12 mb-3">

        @foreach ($feed_items as $feed_item)
          <h5>
            <a target="_new" href="{{ $feed_item['link'] }}" class="card-link">{{ $feed_item['title'] }}</a>
          </h5>
          <h6 class="mb-2">{{ $feed_item['pubDate'] }}</h6>
          {!! $feed_item['description'] !!}
          @if (!$loop->last)
            <hr>
          @endif
        @endforeach
     
    </div>
