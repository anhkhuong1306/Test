async function getListAccount() {
    let params = new URL(document.location.toString()).searchParams;
    let customerId = params.has("customerId") ? params.get("customerId") : '';

    const response = await fetch(`http://localhost:8000/api/accounts?customerId=${customerId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    });
    const result = await response.json();

    if (result?.data !== undefined && result.data.length !== 0) {
        const listAccount = result.data;
        for (let account of listAccount) {
            let tbody = document.getElementsByTagName('tbody')[0];
            let tr = document.createElement('tr');
            tr.innerHTML += `<td>${account.customer_id}</td>`    + 
                            `<td>${account.account_id}</td>`     + 
                            `<td>${account.account_number}</td>` +
                            `<td>${account.balance}</td>`        +
                            `<td><a href="./account.html?accountId=${account.account_id}">transfer</a></td>` +
                            `<td><a href="./transactions.html?accountNumber=${account.account_number}">transactions</a></td>`;
            tbody.append(tr);
        }
    }
}

async function getAccountDetail() {
    let params = new URL(document.location.toString()).searchParams;
    let accountId = params.has("accountId") ? params.get("accountId") : '';

    const response = await fetch(`http://localhost:8000/api/account?accountId=${accountId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    });
    const result = await response.json();

    if (result?.data !== undefined && result.data.length !== 0) {
        const account = result.data;
        let tbody = document.getElementsByTagName('tbody')[0];
        let tr = document.createElement('tr');
        tr.style.height = '15px';
        tr.innerHTML += `<td>${account.account_id}</td>`     + 
                        `<td id="accountTransfer">${account.account_number}</td>` +
                        `<td id="balance">${account.balance}</td>`        +
                        `<td><a href="./transactions.html?accountNumber=${account.account_number}">transactions</a></td>`;
        tbody.append(tr);
    }
}

async function transfer() {
    const accountReceiver = document.getElementsByName('accountNumber')[0].value;
    const amount = document.getElementsByName('amount')[0].value;
    const accountTransfer = document.getElementById('accountTransfer').innerText;
    const balance = document.getElementById('balance').innerText;
    const divAlert = document.getElementsByClassName('alert')[0];

    const response = await fetch(`http://localhost:8000/api/account/transfer`, {
        method: 'POST',
        body: JSON.stringify({
            accountTransfer,
            accountReceiver,
            amount: parseFloat(amount),
            balance: parseFloat(balance)
        }),
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    });
    const result = await response.json();

    if (result?.statusCode !== undefined && result.statusCode === 200) {
        // divAlert.innerHTML = `<p style="color:green;font-size:17px">Transfer money successfully.</p>`;
        alert('Transfer money successfully.');
        location.reload();
    } else {
        // divAlert.innerHTML = `<p style="color:red;font-size:17px">Transfer money failed.</p>`;
        alert('Transfer money failed.');
        location.reload();
    }
}

function addAccount() {
    let params = new URL(document.location.toString()).searchParams;
    let customerId = params.has("customerId") ? params.get("customerId") : '';

    if (!customerId) {
        document.getElementById('new-customer').style.cssText = "background-color:#ccc; cursor:no-drop";
        return;
    } 
    
    document.getElementById('new-customer').setAttribute('onClick', `location.href="./new-account.html?customerId=${customerId}"`);
}

async function getAccountNumber() {
    let params = new URL(document.location.toString()).searchParams;
    let customerId = params.has("customerId") ? params.get("customerId") : '';

    if (!customerId) {
        return;
    } 

    const response = await fetch(`http://localhost:8000/api/account/generate-account-number`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    });
    const result = await response.json();

    if (result?.statusCode !== undefined && 
        result.statusCode === 200 && 
        result?.data !== undefined) {
        
        document.getElementsByName('customerId')[0].value = customerId;
        document.getElementsByName('accountNumber')[0].value = result.data;
    } else {
        alert('Generate account number failed.');
    }
}

async function newAccount() {
    const customerId = document.getElementsByName('customerId')[0].value;
    const accountNumber = document.getElementsByName('accountNumber')[0].value;
    const balance = document.getElementsByName('balance')[0].value;
    const divAlert = document.getElementsByClassName('alert')[0];

    const response = await fetch(`http://localhost:8000/api/accounts`, {
        method: 'POST',
        body: JSON.stringify({
            customerId,
            accountNumber,
            balance: parseFloat(balance)
        }),
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    });
    const result = await response.json();

    if (result?.statusCode !== undefined && result.statusCode === 201) {
        divAlert.innerHTML = `<p style="color:green;font-size:17px;display:flex;justify-content:center;line-height:20px">Create new account successfully.</p>`;
        alert('Create new account successfully.');
    } else {
        divAlert.innerHTML = `<p style="color:red;font-size:17px;display:flex;justify-content:center;line-height:20px">Create new account failed.</p>`;
        alert('Create new account failed.');
    }
}


function handleOnLoad() {
    getListAccount();
    addAccount();
}