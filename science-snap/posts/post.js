window.addEventListener('load', function () {
    const pageContext = window.postPageContext || { isLoggedIn: false, currentUserId: null, currentUserName: null };
    loadPosts();

    async function loadPosts() {
        try {
            const response = await fetch('get_posts.php');
            const posts = await response.json();

            const container = document.getElementById('postContainer');
            container.innerHTML = '';

            posts.forEach(post => {
                const postElement = document.createElement('div');
                postElement.style.border = '1px solid black';
                postElement.style.margin = '10px 0';
                postElement.style.padding = '10px';
                postElement.style.position = 'relative';

                const canManagePost = Number(post.is_owner) === 1;
                const postActions = canManagePost
                    ? `
                    <button class="deleteButton" data-id="${post.id}" style="color: red; cursor: pointer;">
                        Delete Post
                    </button>
                    <button class="editButton" data-id="${post.id}" data-title="${post.title}" data-desc="${post.description}">
                        Edit Post
                    </button>
                `
                    : '';

                postElement.innerHTML = `
                    <h3>${post.title}</h3>
                    <p>${post.description}</p>
                    <br>
                    <small>ID: ${post.id} | Posted by: ${post.username ?? 'Unknown'} | Posted on: ${post.created_at}</small>
                    ${postActions}
                `;

                const deleteButton = postElement.querySelector('.deleteButton');
                if (deleteButton) {
                    deleteButton.addEventListener('click', async function() {
                        const postId = this.getAttribute('data-id');
                        if (confirm('Are you sure you want to delete this post?')) {
                            await deletePost(postId);
                        }
                    });
                }

                const editButton = postElement.querySelector('.editButton');
                if (editButton) {
                    editButton.addEventListener('click', function () {
                        const editPostForm = document.getElementById('editPost');
                        if (!editPostForm) {
                            return;
                        }

                        editPostForm.style.display = 'block';
                        document.getElementById('editId').value = this.getAttribute('data-id');
                        document.getElementById('editName').value = this.getAttribute('data-title');
                        document.getElementById('editDesc').value = this.getAttribute('data-desc');

                        editPostForm.scrollIntoView();
                    });
                }

                container.appendChild(postElement);
            });
        } catch (error) {
            console.error('Error loading posts:', error);
        }
    }

    async function deletePost(id) {
        try {
            const response = await fetch('delete_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id})
            });

            const result = await response.json();
            if (result.status === 'success') {
                loadPosts();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Delete failed:', error);
        }
    }

    const editPostForm = document.getElementById('editPost');

    if (editPostForm) {
        editPostForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (!pageContext.isLoggedIn) {
                alert('You must be logged in to edit posts.');
                return;
            }

            const editData = {
                id: document.getElementById('editId').value,
                title: document.getElementById('editName').value,
                description: document.getElementById('editDesc').value
            };

            try {
                const response = await fetch('edit_post.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(editData)
                });

                const result = await response.json();

                if (result.status === 'success') {
                    editPostForm.style.display = 'none';
                    await loadPosts();
                } else {
                    alert(result.message || 'Unable to edit post.');
                }
            } catch (error) {
                console.error('Edit failed:', error);
            }
        });
    }
});