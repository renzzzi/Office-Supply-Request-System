document.addEventListener("DOMContentLoaded", () => {
    fetch('../api/check-stale-requests.php')
        .then(response => response.json())
        .catch(err => console.error(err));
});