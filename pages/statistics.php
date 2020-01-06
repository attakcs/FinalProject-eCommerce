<h3 class="divider">Statistics</h3>

<form class="report-params" id="frmReportParamss">
    <p>
        <label for="report_code">Report</label>
        <select id="report_code">
            <option value="TopSales">Top Sales</option>
            <option value="TopRated">Top Rated Products</option>
            <option value="LoyalCustomers">Loyal Customers</option>
            <option value="MonthlyIncome">Monthly Income</option>
            <option value="MonthlyInvoices">Monthly Invoices</option>
        </select>
    </p>
    <p>
        <label for="start_date">From</label>
        <input type="date" id="start_date">
    </p>
    <p>
        <label for="end_date">To</label>
        <input type="date" id="end_date">
    </p>
    <p id="limit_param">
        <label for="limit">Limit</label>
        <input type="number" id="limit" min="1" value="5">
    </p>
    <p class="operations">
        <input type="submit" class="button create" value="Generate">
    </p>
</form>

<section class="report-body">
    <table class="data-list top-sales" id="tblTopSales">
        <thead>
            <tr>
                <th data-model="image">Image</th>
                <th data-model="product">Product</th>
                <th data-model="brief">Brief</th>
                <th data-model="quantity">Quantity</th>
                <th data-model="last_purchased">Last Purchased</th>
                <th data-model="status">status</th>
            </tr>
        </thead>
    </table>

    <table class="data-list top-rated" id="tblTopRated">
        <thead>
            <tr>
                <th data-model="image">Image</th>
                <th data-model="product">Product</th>
                <th data-model="brief">Brief</th>
                <th data-model="stars">Stars</th>
                <th data-model="last_rated">Last Rated</th>
                <th data-model="status">status</th>
            </tr>
        </thead>
    </table>

    <table class="data-list loyal-customers" id="tblLoyalCustomers">
        <thead>
            <tr>
                <th data-model="photo">Photo</th>
                <th data-model="customer">Customer</th>
                <th data-model="email">Email</th>
                <th data-model="placed_orders">Orders</th>
                <th data-model="total_paid">Total Paid</th>
            </tr>
        </thead>
    </table>

    <table class="data-list monthly-income" id="tblMonthlyIncome">
        <thead>
            <tr>
                <th data-model="year">Year</th>
                <th data-model="month">Month</th>
                <th data-model="income">Income</th>
                <th data-model="vat">VAT</th>
                <th data-model="last_payment">Last Payment</th>
            </tr>
        </thead>
    </table>

    <table class="data-list monthly-invoices" id="tblMonthlyInvoices">
        <thead>
            <tr>
                <th data-model="year">Year</th>
                <th data-model="month">Month</th>
                <th data-model="pending">Pending</th>
                <th data-model="paid">Paid</th>
                <th data-model="not_paid">Not Paid</th>
                <th data-model="canceled">Canceled</th>
            </tr>
        </thead>
    </table>
</section>

<button class="btn btn-pill btn-dark" onclick="location.href='/Admin-Panel'">&#x2B9C; Back</button>

<script>
    let monthStart = new Date();
    monthStart.setDate(1);
    $('#start_date').value = monthStart.toISOString().split('T')[0];

    let monthEnd = new Date();
    monthEnd.setDate(1);
    monthEnd.setMonth(monthStart.getMonth()+1);
    monthEnd.setDate(-1);
    $('#end_date').value = monthEnd.toISOString().split('T')[0];

    // Hide limit param if not available for selected report
    $('#report_code').addEventListener('change', function(){
        const noLimitRepoert = ['MonthlyIncome', 'MonthlyInvoices'];
        $('#limit_param').style.display = (noLimitRepoert.indexOf(this.value)>-1)?'none':'block';
    });

    $('#frmReportParamss').addEventListener('submit', function(e){
        e.preventDefault();

        const reportCode = $('#report_code').value;
       
        const data = {
            start_date: $('#start_date').value,
            end_date: $('#end_date').value,
            limit: $('#limit').value
        };
        
        GenerateReport(reportCode, data);

        for(tbl of $$('table.data-list')){
            let tbody = $('tbody', tbl);

            if(tbody){
                tbody.innerHTML = '';
            }

            tbl.classList.remove('show');
        }

        $('#tbl'+reportCode).classList.add('show');

    });

    function GenerateReport(reportCode, data){
        Ajax('POST', '/api/Statistics/' + reportCode,
           data,
            function(resp){
                if(ErrorInResponse(resp)){
                    return false;
                }

                 // Clear the table before appending rows
                RenderTable($('#tbl'+reportCode), resp.data, true, function(data){
                    return GenerateRowTemplate(data, reportCode);
                });
            }
        );
    }

    function GenerateRowTemplate(data, reportCode){
        switch(reportCode){
            case 'TopSales':
                template = `
                    <td><img class="product-image" src="/api/Product/Image/${data.image}"></td>
                    <td class="product">${data.product}</td>
                    <td class="brief">${data.brief}</td>
                    <td class="counter">${data.quantity}</td>
                    <td>${data.last_purchased}</td>
                    <td>${data.status}</td>
                `
                break;
            
            case 'TopRated':
                template = `
                    <td><img class="product-image" src="/api/Product/Image/${data.image}"></td>
                    <td class="product">${data.product}</td>
                    <td class="brief">${data.brief}</td>
                    <td class="counter">${data.stars}</td>
                    <td>${data.last_rated}</td>
                    <td>${data.status}</td>
                `
                break;
            
            case 'LoyalCustomers':
                template = `
                    <td><img class="user-photo" src="/api/User/Photo/${data.photo}"></td>
                    <td class="customer">${data.customer}</td>
                    <td>${data.email}</td>
                    <td class="counter">${data.placed_orders}</td>
                    <td class="total-paid"><?= CURRENCY ?>${data.total_paid}</td>
                `
                break;
            
            case 'MonthlyIncome':
                template = `
                    <td>${data.year}</td>
                    <td>${data.month}</td>
                    <td class="income"><?= CURRENCY ?>${data.income}</td>
                    <td class="vat"><?= CURRENCY ?>${data.vat}</td>
                    <td>${data.last_payment}</td>
                `
                break;
            
            case 'MonthlyInvoices':
                template = `
                    <td>${data.year}</td>
                    <td>${data.month}</td>
                    <td>
                        <p class="counter">${data.pending}</p>
                        <p class="amount"><?= CURRENCY ?>${data.pending_amount}</p>
                        <p class="vat">VAT <?= CURRENCY ?>${data.pending_vat}</p>
                    </td>
                    <td>
                        <p class="counter">${data.paid}</p>
                        <p class="amount"><?= CURRENCY ?>${data.paid_amount}</p>
                        <p class="vat">VAT <?= CURRENCY ?>${data.paid_vat}</p>
                    </td>
                    <td>
                        <p class="counter">${data.not_paid}</p>
                        <p class="amount"><?= CURRENCY ?>${data.not_paid_amount}</p>
                        <p class="vat">VAT <?= CURRENCY ?>${data.not_paid_vat}</p>
                    </td>
                    <td>
                        <p class="counter">${data.canceled}</p>
                        <p class="amount"><?= CURRENCY ?>${data.canceled_amount}</p>
                        <p class="vat">VAT <?= CURRENCY ?>${data.canceled_vat}</p>
                    </td>
                `
                break;
        }
      
        return template
    }
</script>

