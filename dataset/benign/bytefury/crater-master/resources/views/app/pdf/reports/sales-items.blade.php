<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sales Item Report</title>
    <style type="text/css">
        body {
            font-family: "DejaVu Sans";
        }
        
        table {
            border-collapse: collapse;
        }

        .sub-container{
            padding: 0px 20px;
        }

        .report-header {
            width: 100%;
        }

        .heading-text {
            font-weight: 600;
            font-size: 24px;
            color: #5851D8;
            width: 100%;
            text-align: left;
            padding: 0px;
            margin: 0px;
        }

        .heading-date-range {
            font-weight: 600;
            font-size: 15px;
            color: #A5ACC1;
            width: 100%;
            text-align: right;
            padding: 0px;
            margin: 0px;
        }

        .sub-heading-text {
            font-weight: 600;
            font-size: 16px;
            line-height: 21px;
            color: #595959;
            padding: 0px;
            margin: 0px;
            margin-top: 30px;
        }

        .sales-items-title {
            margin-top: 20px;
            padding-left: 3px;
            font-size: 16px;
            line-height: 21px;
            color: #040405;
        }

        .items-table-container {
            padding-left: 10px;
        }

        .items-table {
            width: 100%;
            padding-bottom: 10px;
        }

        .item-title {
            padding: 0px;
            margin: 0px;
            font-size: 14px;
            line-height: 21px;
            color: #595959;
        }

        .item-sales-amount {
            padding: 0px;
            margin: 0px;
            font-size: 14px;
            line-height: 21px;
            text-align: right;
            color: #595959;
        }

        .sales-total-indicator-table {
            border-top: 1px solid #EAF1FB;
            width: 100%;
        }

        .sales-total-cell {
            padding-top: 10px;
        }

        .sales-total-amount {
            padding-top: 10px;
            padding-right: 30px;
            padding: 0px;
            margin: 0px;
            text-align: right;
            font-weight: 500;
            font-size: 16px;
            line-height: 21px;
            text-align: right;
            color: #040405;
        }

        .report-footer {
            width: 100%;
            margin-top: 40px;
            padding: 15px 20px;
            background: #F9FBFF;
            box-sizing: border-box;
        }

        .report-footer-label {
            padding: 0px;
            margin: 0px;
            text-align: left;
            font-weight: 600;
            font-size: 16px;
            line-height: 21px;
            color: #595959;
        }

        .report-footer-value {
            padding: 0px;
            margin: 0px;
            text-align: right;
            font-weight: 500;
            font-size: 20px;
            line-height: 21px;
            color: #5851D8;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="sub-container">
        <table class="report-header">
            <tr>
                <td>
                    <p class="heading-text">{{ $company->name }}</p>
                </td>
                <td>
                    <p class="heading-date-range">{{ $from_date }} - {{ $to_date }}</p>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <p class="sub-heading-text text-center">Sales Report: By Item</p>
                </td>
            </tr>
        </table>
        
        <p class="sales-items-title">Items</p>
        @foreach ($items as $item)
            <div class="items-table-container">
                <table class="items-table">
                    <tr>
                        <td>
                            <p class="item-title">
                                {{ $item->name }}
                            </p>
                        </td>
                        <td>
                            <p class="item-sales-amount">
                                {!! format_money_pdf($item->total_amount) !!}
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
        @endforeach

            <table class="sales-total-indicator-table">
                <tr>
                    <td class="sales-total-cell">
                        <p class="sales-total-amount">
                            {!! format_money_pdf($totalAmount) !!}
                        </p>
                    </td>
                </tr>
            </table>
    </div>


    <table class="report-footer">
        <tr>
            <td>
                <p class="report-footer-label">TOTAL SALES</p>
            </td>
            <td>
                <p class="report-footer-value">
                    {!! format_money_pdf($totalAmount) !!}
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
