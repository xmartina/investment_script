<script>
document.addEventListener('DOMContentLoaded', function() {
document.addEventListener('click', function(e) {
if (e.target && e.target.id === 'loadMore') {
const button = e.target;
const page = button.dataset.page;
const userId = button.dataset.userId;

button.disabled = true;
button.innerHTML = 'Loading...';

fetch(`/path/to/transactions-endpoint?page=${page}&user_id=${userId}`)
.then(response => response.json())
.then(data => {
// Append new rows
document.querySelector('#dataTable tbody').insertAdjacentHTML('beforeend', data.rows);

// Update or remove the Load More button
if (data.hasMore) {
button.dataset.page = data.nextPage;
button.disabled = false;
button.innerHTML = 'Load More';
} else {
button.remove();
}
})
.catch(error => {
console.error('Error:', error);
button.disabled = false;
button.innerHTML = 'Load More';
});
}
});
});

</script>