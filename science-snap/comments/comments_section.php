<?php
/**
 * Edgar, Jamie, Noah, Jacob
 * Date Created: 2026-03-31
 * Description: Comments section template - displays comment form and comment thread
 */

// comment form action
$comment_action = '../comments/add_comment.php';
?>

      <?php if ($comment_feedback): ?>
      <p class="page-feedback <?php echo $comment_feedback['type'] === 'error' ? 'feedback-error' : 'feedback-success'; ?>">
        <?php echo htmlspecialchars($comment_feedback['message']); ?>
      </p>
      <?php endif; ?>

      <section class="comments-section">
        <h3 class="section-title">Comments</h3>
        <?php if ($is_logged_in): ?>
        <form action="<?php echo htmlspecialchars($comment_action); ?>" method="post" class="comment-form">
          <input type="hidden" name="add_comment" value="1" />
          <input type="hidden" name="post_id" value="<?php echo htmlspecialchars((string) $post['id']); ?>" />
          <label for="new-comment">Comment</label><br />
          <textarea id="new-comment" name="comment_content" class="post-textarea" rows="4"></textarea>
          <br /><br />
          <button type="submit">Post comment</button>
        </form>
        <?php else: ?>
        <p class="comments-login-hint">Log in to comment.</p>
        <?php endif; ?>

        <?php comments_print_tree('root', $comments_by_parent, $post, $is_logged_in, $comment_action); ?>
      </section>
