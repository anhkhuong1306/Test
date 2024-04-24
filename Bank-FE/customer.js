async function getListCustomer() {
    const response = await fetch('http://localhost:8000/api/customers', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    });
    const result = await response.json();

    if (result?.data !== undefined && result.data.length !== 0) {
        const listCustomers = result.data.data;
        for (let customer of listCustomers) {
            let tbody = document.getElementsByTagName('tbody')[0];
            tbody.insertRow().innerHTML = `<td>${customer.customer_id}</td>` + 
                            `<td>${customer.first_name}</td>`  +
                            `<td>${customer.last_name}</td>`   +
                            `<td>${customer.email}</td>`       +
                            `<td>${customer.phone}</td>`       +
                            `<td><a href="./accounts.html?customerId=${customer.customer_id}">accounts</a></td>`;
        }
        console.log(result.data.data);
    }
}

async function newCustomer() {
    const firstName = document.getElementsByName('firstName')[0].value;
    const lastName = document.getElementsByName('lastName')[0].value;
    const phone = document.getElementsByName('phone')[0].value;
    const email = document.getElementsByName('email')[0].value;
    const divAlert = document.getElementsByClassName('alert')[0];

    const response = await fetch('http://localhost:8000/api/customers', {
        method: 'POST',
        body: JSON.stringify({
            firstName,
            lastName,
            phone,
            email
        }),
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    });
    const result = await response.json();

    if (result?.statusCode !== undefined && result.statusCode === 201) {
        divAlert.innerHTML = `<p style="color:green;font-size:17px;display:flex;justify-content:center;line-height:20px">Create new customer successfully.</p>`;
    } else {
        divAlert.innerHTML = `<p style="color:red;font-size:17px;display:flex;justify-content:center;line-height:20px">Create new customer failed.</p>`;
    }
}