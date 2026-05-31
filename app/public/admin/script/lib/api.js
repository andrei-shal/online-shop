export function touchAPI(endpoint, json) {
    return fetch('/admin/api/'+endpoint+'.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(json)
    });
};