{{--
  This piece of layout describes the global (ie. shared accross all of the site)
  contents of the html <head> section
--}}

<meta charset="utf-8">

<title>
@section('page_title')
{{{ $title }}}
@show
</title>

<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="icon" type="image/png" href="/favicon.png" />

<link rel="alternate" href="{{{ LaravelLocalization::getNonLocalizedURL() }}}" hreflang="x-default" />
@foreach(LaravelLocalization::getSupportedLanguagesKeys() as $localeCode)
<link rel="alternate" href="{{{ LaravelLocalization::getLocalizedURL($localeCode) }}}" hreflang="{{{ $localeCode }}}" />
@endforeach

{{ HTML::style("css/".asset_path("courseadvisor.css")) }}
{{ HTML::style("css/font-awesome.min.css") }}

<script type="application/ld+json">
{
  "@context": "http://schema.org",
  "@type": "WebSite",
  "url": "http://courseadvisor.ch/",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "http://courseadvisor.ch/search?q={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>
