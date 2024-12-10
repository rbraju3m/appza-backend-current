@if($theme->photoGallery)
    @foreach($theme->photoGallery as $image)
        <tr>
            <td width="30%">
                @if($image->image)
                    <a href="{{config('app.image_public_path').$image->image}}" target="_blank">
                        <img src="{{config('app.image_public_path').$image->image}}" alt="{{$image->caption}}" width="25%">
                    </a>
                @endif
            </td>
            <td width="60%">{{$image->caption}}</td>
            <td><button type="button" data-id="{{$image->id}}" class="btn btn-danger btn-sm record_delete">X</button></td>
        </tr>
    @endforeach
@endif


