@extends('mail-template.main')
@section('content')
    <table border="0" cellpadding="0" cellspacing="0" class="force-row" style="width: 100%;    border-bottom: solid 1px #ccc;">
        <tr>
            <td class="content-wrapper" style="padding-left:24px;padding-right:24px"><br>
                <div class="title" style="font-family: Helvetica, Arial, sans-serif; font-size: 18px;font-weight:400;color: #000;text-align: left;
                 padding-top: 20px;">Dear {{$details['customer_name']}} ,</div></td>
        </tr>
        <tr>
            <td class="cols-wrapper" style="padding-left:12px;padding-right:12px"><!--[if mso]>
                <table border="0" width="576" cellpadding="0" cellspacing="0" style="width: 576px;">
                    <tr>
                        <td width="192" style="width: 192px;" valign="top">
                <![endif]-->
                <table border="0" cellpadding="0" cellspacing="0" align="left" class="force-row" style="width: 100%;">
                    <tr>
                        <td class="row" valign="top" style="padding-left:12px;padding-right:12px;padding-top:18px;padding-bottom:12px"><table border="0" cellpadding="0" cellspacing="0" style="width:100%;">
                                <tr>
                                    <td class="subtitle" style="font-family:Helvetica, Arial, sans-serif;font-size:14px;line-height:22px;font-weight:400;color:#333;padding-bottom:30px; text-align: left;">
                                        <p style="color: black">Thank you for submitting your request for the development of ["{{$details['app_name']}}"]. We’re excited to begin the build process and bring your project to life!</p>
                                        <p style="color: black">
                                            Our team is now working diligently to ensure that every detail of the app is aligned with your specifications and meets our high-quality standards. The build process will take approximately [estimated time, e.g., "X business days"], after the process completes we'll be informed throughout.
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-family: Helvetica, Arial, sans-serif;font-size: 14px;line-height: 22px;font-weight: 400;color: #333; padding-bottom: 30px;text-align: left;">Best regards,<br>Thank you for choosing Appza <br>{{config('app.company_name')}}''</td>
                                </tr>
                            </table>
                            <br></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

@endsection
