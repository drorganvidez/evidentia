@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" class="header-link">
{!! $slot !!}
</a>
</td>
</tr>
