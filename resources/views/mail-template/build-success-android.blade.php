Dear {{$details['customer_name']}} ,

<p style="color: black;text-align: justify">
    I’m excited to inform you that the development of your Android app, <b>{{$details['app_name']}}</b> is now complete! 🎉
</p>

<p style="color: black;text-align: justify">
    On the following link you will find your app files.
    <br>
    apk link : <a href="{{$details['apk_url']}}">click here</a>
    {{--<a href="{{ route('download_apk') }}" style="display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">
        Go to Download apk
    </a>--}}
    <br>
    aab link : <a href="{{$details['aab_url']}}">click here</a>
</p>

<p style="color: black;text-align: justify;">
    If you need any assistance or have questions about the app’s features or functionalities, feel free to reach out. We're here to support you every step of the way.
</p>

<p style="color: black">Best regards,
    <br>Thank you for choosing Appza
    <br><span style="color: black">{{config('app.company_name')}}</span>
</p>
