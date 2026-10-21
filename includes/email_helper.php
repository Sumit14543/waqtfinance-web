<?php
declare(strict_types=1);

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

if (!function_exists('send_notification_email')) {
    function send_notification_email(string $subject, string $htmlBody): bool
    {
        try {
            $smtpHost = required_env('SMTP_HOST');
            $smtpPort = (int) (env_value('SMTP_PORT', '465') ?? '465');
            $smtpSecure = env_value('SMTP_SECURE', 'ssl') ?? 'ssl';
            $smtpUsername = required_env('SMTP_USERNAME');
            $smtpPassword = required_env('SMTP_PASSWORD');
            $smtpFromEmail = required_env('SMTP_FROM_EMAIL');
            $smtpFromName = env_value('SMTP_FROM_NAME', 'Waqt Finance') ?? 'Waqt Finance';

            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';

            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUsername;
            $mail->Password = $smtpPassword;
            
            if (strtolower($smtpSecure) === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif (strtolower($smtpSecure) === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
            }

            $mail->Port = $smtpPort;

            // Sender and recipient (sending notification to customercare@waqtfinance.com)
            $mail->setFrom($smtpFromEmail, $smtpFromName);
            $mail->addAddress($smtpUsername); // Send to our customercare email

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;

            $mail->send();
            return true;
        } catch (Throwable $e) {
            error_log("Notification Email Sending Failed: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('send_contact_enquiry_notification')) {
    function send_contact_enquiry_notification(
        string $name,
        string $email,
        string $phone,
        string $subject,
        string $message,
        ?string $ipAddress,
        ?string $userAgent
    ): bool {
        $mailSubject = "Waqt Finance: New Contact Enquiry - " . $subject;

        $safeName = htmlspecialchars($name);
        $safeEmail = htmlspecialchars($email);
        $safePhone = htmlspecialchars($phone);
        $safeSubject = htmlspecialchars($subject);
        $safeMessage = nl2br(htmlspecialchars($message));
        $safeIp = htmlspecialchars($ipAddress ?? 'N/A');
        $safeAgent = htmlspecialchars($userAgent ?? 'N/A');

        $mailBody = "
        <div style='background-color: #f8fafc; padding: 40px 10px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); border: 1px solid #e2e8f0;'>
                
                <!-- Header Banner -->
                <div style='background: linear-gradient(135deg, #062b5d 0%, #18529c 100%); padding: 30px; text-align: center;'>
                    <h1 style='color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 0.5px;'>Waqt Finance</h1>
                    <p style='color: #93c5fd; margin: 5px 0 0 0; font-size: 14px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px;'>Internal Notification Service</p>
                </div>
                
                <!-- Badge & Title -->
                <div style='padding: 30px 30px 20px 30px; text-align: left;'>
                    <div style='margin-bottom: 20px;'>
                        <span style='background-color: #fef3c7; color: #d97706; padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid #fde68a;'>New Contact Inquiry</span>
                    </div>
                    <h2 style='color: #0f172a; margin: 0 0 10px 0; font-size: 20px; font-weight: 700; line-height: 1.3;'>Customer Inquiry Received</h2>
                    <p style='color: #64748b; margin: 0; font-size: 14px; line-height: 1.5;'>A visitor has submitted a new inquiry via the website contact form. The details are documented below:</p>
                </div>
                
                <!-- Details Table -->
                <div style='padding: 0 30px; text-align: left;'>
                    <table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>
                        <tr style='border-bottom: 1px solid #f1f5f9;'>
                            <td style='padding: 12px 10px; font-size: 14px; color: #64748b; width: 35%;'><strong>Applicant Name</strong></td>
                            <td style='padding: 12px 10px; font-size: 14px; color: #0f172a; font-weight: 500;'>{$safeName}</td>
                        </tr>
                        <tr style='border-bottom: 1px solid #f1f5f9;'>
                            <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Email Address</strong></td>
                            <td style='padding: 12px 10px; font-size: 14px; color: #0b5cad; font-weight: 500;'><a href='mailto:{$safeEmail}' style='color: #0b5cad; text-decoration: none;'>{$safeEmail}</a></td>
                        </tr>
                        <tr style='border-bottom: 1px solid #f1f5f9;'>
                            <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Mobile Number</strong></td>
                            <td style='padding: 12px 10px; font-size: 14px; color: #0f172a; font-weight: 500;'>+91 {$safePhone}</td>
                        </tr>
                        <tr style='border-bottom: 1px solid #f1f5f9;'>
                            <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Inquiry Subject</strong></td>
                            <td style='padding: 12px 10px; font-size: 14px; color: #0f172a; font-weight: 500;'>{$safeSubject}</td>
                        </tr>
                    </table>
                </div>
                
                <!-- Message Box -->
                <div style='padding: 20px 30px; text-align: left;'>
                    <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; border-left: 4px solid #18529c;'>
                        <p style='margin: 0 0 10px 0; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;'>Message Details</p>
                        <p style='margin: 0; font-size: 14px; color: #334155; line-height: 1.6; font-style: italic;'>\"{$safeMessage}\"</p>
                    </div>
                </div>

                <!-- Call to Action -->
                <div style='padding: 10px 30px 35px 30px; text-align: center;'>
                    <a href='https://waqtfinance.com/control-panel/login' style='display: inline-block; background-color: #18529c; color: #ffffff; padding: 12px 24px; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none;' target='_blank'>Access CRM Dashboard</a>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #f1f5f9; padding: 25px 30px; text-align: center; border-top: 1px solid #e2e8f0;'>
                    <p style='margin: 0 0 5px 0; font-size: 12px; color: #64748b;'>This is an automated system alert from Waqt Finance.</p>
                    <p style='margin: 0; font-size: 10px; color: #94a3b8; line-height: 1.4;'>
                        Submitted from IP: {$safeIp} | Device: {$safeAgent}<br>
                        CONFIDENTIALITY NOTICE: The information in this email is confidential and intended solely for internal administrative review.
                    </p>
                </div>
                
            </div>
        </div>
        ";

        return send_notification_email($mailSubject, $mailBody);
    }
}

if (!function_exists('send_loan_application_notification')) {
    function send_loan_application_notification(mysqli $conn, string $loanType, string $application_id, string $displayId): bool
    {
        $subject = "Waqt Finance: New " . ucfirst($loanType) . " Loan Application - " . $displayId;
        
        $safeDisplayId = htmlspecialchars($displayId);
        $safeLoanType = htmlspecialchars(ucfirst($loanType));
        $loanBadgeText = htmlspecialchars(strtoupper($loanType) . " LEAD");
        
        $dynamicRows = "";
        
        if ($loanType === 'payday') {
            $stmt = $conn->prepare("SELECT * FROM loan_applications WHERE application_id = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("s", $application_id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($row) {
                    $dynamicRows = "
                    <tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Applicant Name</strong></td>
                        <td style='padding: 12px 10px; font-size: 14px; color: #0f172a; font-weight: 500;'>" . htmlspecialchars($row['full_name'] ?? '') . "</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Mobile Number</strong></td>
                        <td style='padding: 12px 10px; font-size: 14px; color: #0f172a; font-weight: 500;'>+91 " . htmlspecialchars($row['mobile'] ?? '') . "</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Email Address</strong></td>
                        <td style='padding: 12px 10px; font-size: 14px; color: #0b5cad; font-weight: 500;'><a href='mailto:" . htmlspecialchars($row['email'] ?? '') . "' style='color: #0b5cad; text-decoration: none;'>" . htmlspecialchars($row['email'] ?? '') . "</a></td>
                    </tr>
                    <tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Requested Amount</strong></td>
                        <td style='padding: 12px 10px; font-size: 14px; color: #15803d; font-weight: 700;'>&#8377;" . number_format((float)($row['loan_amount'] ?? 0)) . "</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Applicant City</strong></td>
                        <td style='padding: 12px 10px; font-size: 14px; color: #0f172a; font-weight: 500;'>" . htmlspecialchars($row['city'] ?? '') . "</td>
                    </tr>
                    ";
                }
            }
        } elseif ($loanType === 'edi') {
            $stmt = $conn->prepare("SELECT * FROM edi_loan_applications WHERE application_id = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("s", $application_id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($row) {
                    $dynamicRows = "
                    <tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Applicant Name</strong></td>
                        <td style='padding: 12px 10px; font-size: 14px; color: #0f172a; font-weight: 500;'>" . htmlspecialchars($row['applicant_name'] ?? '') . "</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Mobile Number</strong></td>
                        <td style='padding: 12px 10px; font-size: 14px; color: #0f172a; font-weight: 500;'>+91 " . htmlspecialchars($row['mobile_number'] ?? '') . "</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Email Address</strong></td>
                        <td style='padding: 12px 10px; font-size: 14px; color: #0b5cad; font-weight: 500;'><a href='mailto:" . htmlspecialchars($row['email'] ?? '') . "' style='color: #0b5cad; text-decoration: none;'>" . htmlspecialchars($row['email'] ?? '') . "</a></td>
                    </tr>
                    <tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Requested Amount</strong></td>
                        <td style='padding: 12px 10px; font-size: 14px; color: #15803d; font-weight: 700;'>&#8377;" . number_format((float)($row['loan_amount'] ?? 0)) . "</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Shop/Enterprise</strong></td>
                        <td style='padding: 12px 10px; font-size: 14px; color: #0f172a; font-weight: 500;'>" . htmlspecialchars($row['shop_business_name'] ?? '') . "</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Applicant City</strong></td>
                        <td style='padding: 12px 10px; font-size: 14px; color: #0f172a; font-weight: 500;'>" . htmlspecialchars($row['city'] ?? '') . "</td>
                    </tr>
                    ";
                }
            }
        } else {
            $stmt = $conn->prepare("SELECT * FROM loan_applied_master WHERE application_id = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("s", $application_id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($row) {
                    $dynamicRows = "
                    <tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Applicant Name</strong></td>
                        <td style='padding: 12px 10px; font-size: 14px; color: #0f172a; font-weight: 500;'>" . htmlspecialchars($row['applicant_name'] ?? '') . "</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Mobile Number</strong></td>
                        <td style='padding: 12px 10px; font-size: 14px; color: #0f172a; font-weight: 500;'>+91 " . htmlspecialchars($row['mobile_number'] ?? '') . "</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Pancard Number</strong></td>
                        <td style='padding: 12px 10px; font-size: 14px; color: #0f172a; font-weight: 500; text-transform: uppercase;'>" . htmlspecialchars($row['pancard_number'] ?? '') . "</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Requested Amount</strong></td>
                        <td style='padding: 12px 10px; font-size: 14px; color: #15803d; font-weight: 700;'>&#8377;" . number_format((float)($row['loan_amount'] ?? 0)) . "</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #f1f5f9;'>
                        <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Applicant City</strong></td>
                        <td style='padding: 12px 10px; font-size: 14px; color: #0f172a; font-weight: 500;'>" . htmlspecialchars($row['city'] ?? '') . "</td>
                    </tr>
                    ";
                }
            }
        }

        $mailBody = "
        <div style='background-color: #f8fafc; padding: 40px 10px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); border: 1px solid #e2e8f0;'>
                
                <!-- Header Banner -->
                <div style='background: linear-gradient(135deg, #062b5d 0%, #0b5cad 100%); padding: 30px; text-align: center;'>
                    <h1 style='color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 0.5px;'>Waqt Finance</h1>
                    <p style='color: #93c5fd; margin: 5px 0 0 0; font-size: 14px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px;'>Application Processing Service</p>
                </div>
                
                <!-- Badge & Title -->
                <div style='padding: 30px 30px 20px 30px; text-align: left;'>
                    <div style='margin-bottom: 20px;'>
                        <span style='background-color: #dcfce7; color: #15803d; padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid #bbf7d0;'>{$loanBadgeText}</span>
                    </div>
                    <h2 style='color: #0f172a; margin: 0 0 10px 0; font-size: 20px; font-weight: 700; line-height: 1.3;'>New Loan Application</h2>
                    <p style='color: #64748b; margin: 0; font-size: 14px; line-height: 1.5;'>A new online loan application has been registered successfully. Below is the parsed application metadata:</p>
                </div>
                
                <!-- Details Table -->
                <div style='padding: 0 30px; text-align: left;'>
                    <table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>
                        <tr style='background-color: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;'>
                            <td style='padding: 12px 10px; font-size: 14px; color: #0f172a; font-weight: 700; width: 35%;'>Application Ref ID</td>
                            <td style='padding: 12px 10px; font-size: 16px; color: #0b5cad; font-weight: 700;'>{$safeDisplayId}</td>
                        </tr>
                        <tr style='border-bottom: 1px solid #f1f5f9;'>
                            <td style='padding: 12px 10px; font-size: 14px; color: #64748b;'><strong>Loan Category</strong></td>
                            <td style='padding: 12px 10px; font-size: 14px; color: #0f172a; font-weight: 500;'>{$safeLoanType}</td>
                        </tr>
                        {$dynamicRows}
                    </table>
                </div>

                <!-- Action Button -->
                <div style='padding: 30px; text-align: center;'>
                    <a href='https://waqtfinance.com/control-panel/login' style='display: inline-block; background-color: #0b5cad; color: #ffffff; padding: 12px 24px; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none;' target='_blank'>View Application Details</a>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #f1f5f9; padding: 25px 30px; text-align: center; border-top: 1px solid #e2e8f0;'>
                    <p style='margin: 0 0 5px 0; font-size: 12px; color: #64748b;'>This is a system-generated secure notification from Waqt Finance.</p>
                    <p style='margin: 0; font-size: 10px; color: #94a3b8; line-height: 1.4;'>
                        CONFIDENTIALITY NOTICE: The information contained in this message may be legally privileged and confidential. Any unauthorized dissemination, distribution, or copying is strictly prohibited.
                    </p>
                </div>
                
            </div>
        </div>
        ";

        return send_notification_email($subject, $mailBody);
    }
}
