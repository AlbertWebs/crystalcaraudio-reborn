{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ url('/') }}</loc><changefreq>daily</changefreq><priority>1.0</priority></url>
    @foreach($paths as $path)<url><loc>{{ url($path) }}</loc><changefreq>weekly</changefreq><priority>0.7</priority></url>@endforeach
</urlset>
