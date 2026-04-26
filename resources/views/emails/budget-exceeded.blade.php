<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Exceeded</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f4f4;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #FF2157 0%, #FF6B6B 100%); padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700;">💰 Finance Tracker</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px 0; font-size: 16px; color: #333333;">Hi {{ $userName }},</p>
                            
                            <p style="margin: 0 0 20px 0; font-size: 16px; color: #333333;">
                                <strong>⚠️ Alert:</strong> You've exceeded your budget for <strong>{{ $categoryIcon }} {{ $categoryName }}</strong> this month.
                            </p>
                            
                            <!-- Budget Summary Card -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #ffebee; border-radius: 8px; overflow: hidden; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 25px;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td style="padding-bottom: 15px;">
                                                    <span style="font-size: 14px; color: #666666; text-transform: uppercase; letter-spacing: 0.5px;">Budget Status</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 20px;">
                                                    <div style="background-color: #e0e0e0; border-radius: 10px; height: 24px; overflow: hidden;">
                                                        <div style="background: linear-gradient(90deg, #FF2157 0%, #FF6B6B 100%); height: 100%; width: 100%; border-radius: 10px;"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                        <tr>
                                                            <td style="width: 50%; padding-right: 10px;">
                                                                <span style="font-size: 14px; color: #666666;">Spent</span><br>
                                                                <span style="font-size: 20px; font-weight: 700; color: #FF2157;">${{ number_format($amountSpent, 2) }}</span>
                                                            </td>
                                                            <td style="width: 50%; padding-left: 10px;">
                                                                <span style="font-size: 14px; color: #666666;">Limit</span><br>
                                                                <span style="font-size: 20px; font-weight: 700; color: #333333;">${{ number_format($budgetLimit, 2) }}</span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 0 0 20px 0; font-size: 16px; color: #333333;">
                                You've gone over by <strong style="color: #FF2157;">${{ number_format($exceededAmount, 2) }}</strong>.
                            </p>
                            
                            <!-- Suggestion Box -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fff3e0; border-left: 4px solid #FF9800; border-radius: 4px; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="margin: 0; font-size: 15px; color: #333333;">
                                            <strong>📋 Suggestion:</strong> We recommend reviewing your recent transactions in this category to understand what drove the overspending. Consider adjusting your budget or finding ways to reduce expenses for the rest of the month.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- CTA Button -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 30px 0;">
                                <tr>
                                    <td style="text-align: center;">
                                        <a href="https://finance-tracking-frontend-three.vercel.app/transactions" style="display: inline-block; background: linear-gradient(135deg, #FF2157 0%, #FF6B6B 100%); color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: 600;">Review Transactions</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 20px 0 0 0; font-size: 14px; color: #666666;">
                                Don't worry—overspending happens. Use this as an opportunity to adjust and get back on track! 💪
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f8f8; padding: 25px 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0 0 10px 0; font-size: 14px; color: #666666;">
                                This is an automated notification from Finance Tracker.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #999999;">
                                You received this email because you have budget alerts enabled for your account.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
