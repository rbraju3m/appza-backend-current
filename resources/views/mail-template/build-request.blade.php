
    Dear {{$details['customer_name']}} ,
    <p style="color: black;text-align: justify">Thank you for submitting your request for
        @if($details['is_android'] && $details['is_ios'])
            {{' Android & iOS '}}
        @elseif($details['is_android'])
            {{' Android '}}
        @elseif($details['is_ios'])
            {{' iOS '}}
        @endif
        App, the development of <b>{{$details['app_name']}}</b>. We’re excited to begin the build process and bring your project to life!</p>
    <p style="color: black;text-align: justify">Our team is now working diligently to ensure that every detail of the app is aligned with your specifications and meets our high-quality standards. The build process will take approximately 15 minutes, after the process completes we'll be informed throughout.</p>
    <p style="color: black">Best regards,
        <br>Thank you for choosing Appza
        <br><span style="color: black">{{config('app.company_name')}}</span>
    </p>
