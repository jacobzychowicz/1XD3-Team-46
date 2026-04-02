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

                postElement.innerHTML = `
                    <h3>${post.title}</h3>
                    <p>${post.description}</p>
                    <br>
                    <small>Posted on: ${post.date_created}</small>
                `;
                container.appendChild(postElement);
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
                postForm.reset();
            } else {
                console.error('Server Error:', response.statusText);
            }
        } catch (error) {
            console.error('Network Error:', error);
        }
    });
});