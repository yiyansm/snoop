// app.js - manejo de likes con fetch (AJAX)
document.addEventListener('click', function(e) {
  if (e.target.matches('.like-btn')) {
    const btn = e.target;
    const card = btn.closest('.card');
    const postId = parseInt(card.getAttribute('data-post-id'), 10);
    const liked = btn.getAttribute('data-liked') === '1';

    const action = liked ? 'unlike' : 'like';
    fetch('like.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ post_id: postId, action: action })
    }).then(r => r.json()).then(data => {
      if (data.ok) {
        btn.setAttribute('data-liked', liked ? '0' : '1');
        const span = card.querySelector('.likes-count');
        if (span) span.textContent = data.likes;
      } else {
        alert(data.error || 'Error en la acción');
      }
    }).catch(err => {
      console.error(err);
      alert('Error en la petición');
    });
  }
}, false);
