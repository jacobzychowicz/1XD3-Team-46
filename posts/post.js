window.addEventListener('load', function () {
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

                postElement.innerHTML = `
                    <h3>${post.title}</h3>
                    <p>${post.description}</p>
                    <br>
                    <small>ID: ${post.id} | Posted on: ${post.created_at}</small>
                    <button class="deleteButton" data-id="${post.id}" style="color: red; cursor: pointer;">
                        Delete Post
                    </button> 
                    <button class="editButton" data-id="${post.id}" data-title="${post.title}" data-desc="${post.description}">
                        Edit Post
                    </button>
                `;

                const deleteButton = postElement.querySelector('.deleteButton');
                deleteButton.addEventListener('click', async function() {
                    const postId = this.getAttribute('data-id');
                    if (confirm('Are you sure you want to delete this post?')) {
                        await deletePost(postId);
                    }
                });

                const editButton = postElement.querySelector('.editButton');
                editButton.addEventListener('click', function () {
                    document.getElementById('editPost').style.display = 'block';
                    document.getElementById('editId').value = this.getAttribute('data-id');
                    document.getElementById('editName').value = this.getAttribute('data-title');
                    document.getElementById('editDesc').value = this.getAttribute('data-desc');

                    document.getElementById('editPost').scrollIntoView();
                });

                container.appendChild(postElement);
                if (window.CommentsUI) {
                    window.CommentsUI.attachToPost(postElement, post.id);
                }
            });
        } catch (error) {
            console.error('Error loading posts:', error);
        }
    }

    addPostForm = document.getElementById('addPost');

    addPostForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const postName = document.getElementById('postName').value;
        const postDesc = document.getElementById('postDesc').value;
        const postAttach = document.getElementById('postAttach').value;

        const postData = {
            title: postName, 
            description: postDesc,
            attachment: postAttach,
            user_id: 1
        };

        try {
            const response = await fetch('add_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(postData)
            });

            if (response.ok) {
                await loadPosts();
                addPostForm.reset();
            } else {
                console.error('Server Error:', response.statusText);
            }
        } catch (error) {
            console.error('Network Error:', error);
        }
    });

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

    editPostForm.addEventListener('submit', async function (event) {
        event.preventDefault();

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

            if (response.ok) {
                editPostForm.style.display = 'none';
                await loadPosts();
            }
        } catch (error) {
            console.error('Edit failed:', error);
        }
    });
});
