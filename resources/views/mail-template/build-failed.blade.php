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
                                        <p style="color: black">I hope this message finds you well. I wanted to provide you with an update regarding the development of your app, [{{$details['app_name']}}]. Unfortunately, we encountered some unexpected issues during the build process, and we were unable to successfully complete the build at this time.</p>
                                        <p style="color: black">
                                            Please go to the “Build App” steps again & try with proper information. Hopefully this time you will find you app successfully
                                        </p>
                                        <p style="color: black">
                                            Thank you for your patience and understanding as we work through this.
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
