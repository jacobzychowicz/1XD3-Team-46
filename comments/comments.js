(function () {
    const COMMENTS_BASE = '../comments/';
    const DEFAULT_USER_ID = 1;

    function escapeHtml(text) {
        if (text === null || text === undefined) {
            return '';
        }
        const div = document.createElement('div');
        div.textContent = String(text);
        return div.innerHTML;
    }

    function buildCommentTree(flat) {
        if (!Array.isArray(flat)) {
            return [];
        }
        const byId = {};
        flat.forEach(function (c) {
            byId[c.id] = Object.assign({}, c, { children: [] });
        });
        const roots = [];
        flat.forEach(function (c) {
            const node = byId[c.id];
            const pid = c.parent_comment_id;
            if (pid != null && pid !== '' && byId[pid]) {
                byId[pid].children.push(node);
            } else {
                roots.push(node);
            }
        });
        function sortRec(n) {
            n.children.sort(function (a, b) {
                return new Date(a.created_at) - new Date(b.created_at);
            });
            n.children.forEach(sortRec);
        }
        roots.sort(function (a, b) {
            return new Date(a.created_at) - new Date(b.created_at);
        });
        roots.forEach(sortRec);
        return roots;
    }

    function appendCommentNode(parentEl, node, postId, userId, reloadComments) {
        const wrap = document.createElement('div');
        wrap.className = 'commentItem';
        wrap.style.marginTop = '10px';
        wrap.style.padding = '6px 0';

        const author = escapeHtml(node.author_name || ('User ' + node.user_id));
        const date = escapeHtml(node.created_at);

        const header = document.createElement('p');
        header.style.margin = '0 0 4px 0';
        header.innerHTML = '<strong>' + author + '</strong> <small>' + date + '</small>';

        const body = document.createElement('p');
        body.style.margin = '0 0 6px 0';
        body.textContent = node.content;

        const replyToggle = document.createElement('button');
        replyToggle.type = 'button';
        replyToggle.textContent = 'Reply';
        replyToggle.style.cursor = 'pointer';

        const replyWrap = document.createElement('div');
        replyWrap.style.display = 'none';
        replyWrap.style.marginTop = '6px';

        const replyForm = document.createElement('form');
        replyForm.className = 'replyForm';

        const replyTa = document.createElement('textarea');
        replyTa.placeholder = 'Write a reply...';
        replyTa.required = true;
        replyTa.style.display = 'block';
        replyTa.style.width = '100%';
        replyTa.style.marginBottom = '6px';

        const submitReply = document.createElement('button');
        submitReply.type = 'submit';
        submitReply.textContent = 'Submit reply';

        const cancelReply = document.createElement('button');
        cancelReply.type = 'button';
        cancelReply.textContent = 'Cancel';
        cancelReply.style.marginLeft = '6px';

        replyForm.appendChild(replyTa);
        replyForm.appendChild(submitReply);
        replyForm.appendChild(cancelReply);
        replyWrap.appendChild(replyForm);

        replyToggle.addEventListener('click', function () {
            replyWrap.style.display = replyWrap.style.display === 'none' ? 'block' : 'none';
        });
        cancelReply.addEventListener('click', function () {
            replyWrap.style.display = 'none';
            replyForm.reset();
        });
        replyForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const text = replyTa.value.trim();
            if (!text) {
                return;
            }
            try {
                const response = await fetch(COMMENTS_BASE + 'add_comment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        content: text,
                        post_id: postId,
                        user_id: userId,
                        parent_comment_id: node.id
                    })
                });
                const result = await response.json();
                if (result.status === 'success') {
                    replyTa.value = '';
                    replyWrap.style.display = 'none';
                    await reloadComments();
                } else {
                    alert(result.message || 'Could not post reply');
                }
            } catch (err) {
                console.error('Reply failed:', err);
            }
        });

        const repliesDiv = document.createElement('div');
        repliesDiv.className = 'commentReplies';
        repliesDiv.style.marginLeft = '20px';
        repliesDiv.style.borderLeft = '2px solid #ccc';
        repliesDiv.style.paddingLeft = '10px';

        wrap.appendChild(header);
        wrap.appendChild(body);
        wrap.appendChild(replyToggle);
        wrap.appendChild(replyWrap);
        wrap.appendChild(repliesDiv);

        parentEl.appendChild(wrap);

        node.children.forEach(function (child) {
            appendCommentNode(repliesDiv, child, postId, userId, reloadComments);
        });
    }

    async function refreshCommentsList(postElement, postId, userId) {
        const list = postElement.querySelector('.commentsList');
        if (!list) {
            return;
        }
        list.innerHTML = '';
        try {
            const response = await fetch(
                COMMENTS_BASE + 'get_comments.php?post_id=' + encodeURIComponent(postId)
            );
            const data = await response.json();
            if (data.error) {
                list.innerHTML = '<p>Could not load comments.</p>';
                console.error(data.error);
                return;
            }
            if (!Array.isArray(data)) {
                list.innerHTML = '<p>Could not load comments.</p>';
                return;
            }
            const tree = buildCommentTree(data);
            const reload = function () {
                return refreshCommentsList(postElement, postId, userId);
            };
            tree.forEach(function (node) {
                appendCommentNode(list, node, postId, userId, reload);
            });
            if (tree.length === 0) {
                list.innerHTML = '<p><small>No comments yet.</small></p>';
            }
        } catch (error) {
            console.error('Error loading comments:', error);
            list.innerHTML = '<p>Could not load comments.</p>';
        }
    }

    function setupCommentsForPost(postElement, postId, userId) {
        const form = postElement.querySelector('.addCommentForm');
        if (!form) {
            return;
        }
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const textarea = form.querySelector('.commentContent');
            const content = textarea.value.trim();
            if (!content) {
                return;
            }
            try {
                const response = await fetch(COMMENTS_BASE + 'add_comment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        content: content,
                        post_id: postId,
                        user_id: userId
                    })
                });
                const result = await response.json();
                if (result.status === 'success') {
                    textarea.value = '';
                    await refreshCommentsList(postElement, postId, userId);
                } else {
                    alert(result.message || 'Could not post comment');
                }
            } catch (error) {
                console.error('Comment failed:', error);
            }
        });
        refreshCommentsList(postElement, postId, userId);
    }

    /**
     * Appends the comment section under a post card and wires fetch/submit.
     * @param {HTMLElement} postElement
     * @param {number|string} postId
     * @param {{ userId?: number }} [options]
     */
    function attachToPost(postElement, postId, options) {
        const userId = (options && options.userId) != null ? options.userId : DEFAULT_USER_ID;
        const section = document.createElement('div');
        section.className = 'commentsSection';
        section.style.marginTop = '16px';
        section.style.borderTop = '1px solid #999';
        section.style.paddingTop = '12px';
        section.innerHTML =
            '<h4 style="margin: 0 0 8px 0;">Comments</h4>' +
            '<div class="commentsList"></div>' +
            '<form class="addCommentForm" style="margin-top: 10px;">' +
            '<textarea class="commentContent" placeholder="Write a comment..." required ' +
            'style="display: block; width: 100%; min-height: 60px;"></textarea>' +
            '<button type="submit" style="margin-top: 6px;">Post comment</button>' +
            '</form>';
        postElement.appendChild(section);
        setupCommentsForPost(postElement, postId, userId);
    }

    window.CommentsUI = { attachToPost: attachToPost };
})();
