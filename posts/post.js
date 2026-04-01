window.addEventListener('load', function () {
    addPostForm = document.getElementById('addPost');

    addPostForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const postName = document.getElementById('postName').value;
        const postData = {
            title: postName
        };

        try {
            const response = await fetch('add_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(postData)
            });

            if (response.ok) {
                const result = await response.json();
                alert('Post Created Successfully');
            } else {
                console.error('Server Error:', response.statusText);
            }
        } catch (error) {
            console.error('Network Error:', error);
        }
    });
});