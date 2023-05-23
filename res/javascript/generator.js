function send() {
    const text = document.querySelector("textarea").value;
    const file = new Blob([text], { type: 'text/plain' });
    const formData = new FormData();
    formData.append('file', file);
    fetch('../php/scripts.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(data => {
            console.log(data);
        })
        .catch(error => {
            console.error('Erro:', error);
        });
}