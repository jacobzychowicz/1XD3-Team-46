<?php

// load comments for one post and sort them into groups by parent id (for threading)
function comments_load_tree($pdo, $post_id)
{
  $stmt = $pdo->prepare(
    'SELECT comments.*, users.username AS comment_username
     FROM comments
     LEFT JOIN users ON comments.user_id = users.id
     WHERE comments.post_id = :post_id
     ORDER BY comments.created_at ASC'
  );
  $stmt->execute([':post_id' => $post_id]);

  $all_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $comments_by_parent = [];

  foreach ($all_rows as $row) {
    $parent = $row['parent_comment_id'];
    if ($parent === null || $parent === '') {
      $group_key = 'root';
    } else {
      $group_key = (int) $parent;
    }
    if (!isset($comments_by_parent[$group_key])) {
      $comments_by_parent[$group_key] = [];
    }
    $comments_by_parent[$group_key][] = $row;
  }

  return $comments_by_parent;
}

// print one branch of the tree (root uses key "root", then comment ids)
function comments_print_tree($parent_key, $comments_by_parent, $post, $is_logged_in, $comment_action)
{
  if (empty($comments_by_parent[$parent_key])) {
    return;
  }

  foreach ($comments_by_parent[$parent_key] as $comment_row) {
    $comment_id = (int) $comment_row['id'];
    $username = $comment_row['comment_username'] ?? 'Unknown';
    $panel_id = 'reply-panel-' . $comment_id;
    $post_id_esc = htmlspecialchars((string) $post['id']);
    $action_esc = htmlspecialchars($comment_action);
    ?>
        <div class="comment-item">
          <p class="comment-body"><?php echo nl2br(htmlspecialchars($comment_row['content'])); ?></p>
          <small>
            <?php echo htmlspecialchars($username); ?> |
            <?php echo htmlspecialchars((string) $comment_row['created_at']); ?>
          </small>
          <?php if ($is_logged_in): ?>
          <div class="comment-reply-actions">
            <button type="button" class="comment-reply-toggle" data-target="<?php echo htmlspecialchars($panel_id); ?>">Reply</button>
          </div>
          <div id="<?php echo htmlspecialchars($panel_id); ?>" class="comment-reply-panel" hidden>
            <form action="<?php echo $action_esc; ?>" method="post" class="comment-reply-form">
              <input type="hidden" name="add_comment" value="1" />
              <input type="hidden" name="post_id" value="<?php echo $post_id_esc; ?>" />
              <input type="hidden" name="parent_comment_id" value="<?php echo htmlspecialchars((string) $comment_id); ?>" />
              <label for="reply-<?php echo htmlspecialchars((string) $comment_id); ?>">Your reply</label><br />
              <textarea id="reply-<?php echo htmlspecialchars((string) $comment_id); ?>" name="comment_content" class="post-textarea" rows="3"></textarea>
              <br /><br />
              <button type="submit">Submit reply</button>
              <button type="button" class="comment-reply-cancel">Cancel</button>
            </form>
          </div>
          <?php endif; ?>
          <div class="comment-replies">
            <?php comments_print_tree($comment_id, $comments_by_parent, $post, $is_logged_in, $comment_action); ?>
          </div>
        </div>
    <?php
  }
}
