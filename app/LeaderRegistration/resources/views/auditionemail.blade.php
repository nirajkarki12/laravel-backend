<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Leader Registration</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body style="margin: 0; padding: 0;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%"> 
        <tr>
            <td style="padding: 10px 0 30px 0;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #cccccc; border-collapse: collapse;">
                    <tr>
                        <td align="center" bgcolor="#360096" style="padding: 40px 0 30px 0; color: #153643; font-size: 28px; font-weight: bold; font-family: Arial, sans-serif;">
                            <img src="{{ $email_data->site_icon }}" width="155" height='155' alt='Logo'  data-default="placeholder" />
                        </td>
                    </tr>
                    @if($email_data)
                    <tr>
                        <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="color: #153643; font-family: Arial, sans-serif; font-size: 24px;">
                                        <b>The Leader Registration Details</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                        <h2 >Dear @if(isset($email_data))  
                                            {{$email_data->name}}
                                        @endif</h2>
                                            <p>Please note this detail for future use.</p><br>
                                            <table border="1" width="100%" cellspacing="0">
                                                <tr>
                                                    <td>Full Name</td><td>{{$email_data->name}}</td>
                                                </tr>
                                                <tr>
                                                    <td>Email</td><td>{{$email_data->email}}</td>
                                                </tr>
                                                <tr>
                                                    <td>Address</td><td>{{$email_data->address}}</td>
                                                </tr>

                                                <tr>
                                                    <td>Gender</td><td>{{ucfirst($email_data->gender)}}</td>
                                                </tr>

                                                <tr>
                                                    <td>Status</td><td>{{$email_data->payment_status==1?'Paid':'Unpaid'}}</td>
                                                </tr>
                                                <tr>
                                                    <td>Reg. Code</td><td>{{$email_data->registration_code?$email_data->registration_code:'Not Available'}}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"><a href="http://gundruknetwork.com/the_leader_audition/web/audition/register">Get your reciept</a></td>
                                                </tr>
                                            </table>
                                            
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                
                                                <td style="font-size: 0; line-height: 0;" width="20">
                                                    &nbsp;
                                                </td>
                                                <td width="260" valign="top">
                                                    
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif


                    <tr>
                        <td bgcolor="#a90c00" style="padding: 30px 30px 30px 30px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;" width="75%">
                                        &reg; Gundruk Networks<br/>
                                    </td>
                                    <td align="right" width="25%">
                                        <table border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="font-family: Arial, sans-serif; font-size: 12px; font-weight: bold;">
                                                    <a href="https://www.twitter.com/bharyang" style="color: #ffffff;">
                                                        Facebook
                                                    </a>
                                                </td>
                                                <td style="font-size: 0; line-height: 0;" width="20">&nbsp;</td>
                                                <td style="font-family: Arial, sans-serif; font-size: 12px; font-weight: bold;">
                                                    <a href="https://www.facebook.com/bharyang" style="color: #ffffff;">
                                                        Twitter    
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>