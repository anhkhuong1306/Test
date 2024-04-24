async function getListTransaction() {
    let params = new URL(document.location.toString()).searchParams;
    let accountNumber = params.has("accountNumber") ? params.get("accountNumber") : '';

    const response = await fetch(`http://localhost:8000/api/transactions?accountNumber=${accountNumber}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    });
    const result = await response.json();

    if (result?.data !== undefined && result.data.length !== 0) {
        const listTransaction = result.data;
        for (let transaction of listTransaction) {
            let tbody = document.getElementsByTagName('tbody')[0];
            let tr = document.createElement('tr');
            tr.style.height = '15px';
            tr.innerHTML += `<td>${transaction.transaction_id}</td>`   + 
                            `<td>${transaction.account_transfer}</td>` +
                            `<td>${transaction.account_receiver}</td>` +
                            `<td>${transaction.amount}</td>`           +
                            `<td>${transaction.date_transfer}</td>`;
            tbody.append(tr);
        }
    }
}

window.onload = getListTransaction();
