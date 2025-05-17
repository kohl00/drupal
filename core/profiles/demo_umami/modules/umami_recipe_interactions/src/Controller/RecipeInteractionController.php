<?php

declare(strict_types=1);

namespace Drupal\umami_recipe_interactions\Controller;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles recipe rating and favorites.
 */
class RecipeInteractionController extends ControllerBase {

  protected Connection $database;
  protected AccountProxyInterface $currentUser;
  protected CsrfTokenGenerator $csrfToken;

  public function __construct(Connection $database, AccountProxyInterface $current_user, CsrfTokenGenerator $csrfToken) {
    $this->database = $database;
    $this->currentUser = $current_user;
    $this->csrfToken = $csrfToken;
  }

  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('database'),
      $container->get('current_user'),
      $container->get('csrf_token')
    );
  }

  /**
   * Rate a recipe.
   */
  public function rate(NodeInterface $node, Request $request): JsonResponse {
    $rating = (int) $request->request->get('rating');
    if ($rating < 1 || $rating > 5) {
      return new JsonResponse(['status' => 'error', 'message' => 'Invalid rating'], 400);
    }
    $uid = $this->currentUser->id();
    $exists = $this->database->select('recipe_rating', 'r')
      ->fields('r', ['rid'])
      ->condition('nid', $node->id())
      ->condition('uid', $uid)
      ->execute()
      ->fetchField();
    if ($exists) {
      $this->database->update('recipe_rating')
        ->fields([
          'rating' => $rating,
          'timestamp' => time(),
        ])
        ->condition('rid', $exists)
        ->execute();
    }
    else {
      $this->database->insert('recipe_rating')
        ->fields([
          'nid' => $node->id(),
          'uid' => $uid,
          'rating' => $rating,
          'timestamp' => time(),
        ])
        ->execute();
    }
    $average = $this->database->select('recipe_rating', 'r')
      ->condition('nid', $node->id())
      ->addExpression('AVG(rating)', 'avg')
      ->execute()
      ->fetchField();
    return new JsonResponse(['status' => 'success', 'average' => round((float) $average, 2)]);
  }

  /**
   * Toggle favorite status.
   */
  public function favorite(NodeInterface $node): JsonResponse {
    $uid = $this->currentUser->id();
    $exists = $this->database->select('recipe_favorite', 'f')
      ->fields('f', ['fid'])
      ->condition('nid', $node->id())
      ->condition('uid', $uid)
      ->execute()
      ->fetchField();
    if ($exists) {
      $this->database->delete('recipe_favorite')
        ->condition('fid', $exists)
        ->execute();
      $favorited = FALSE;
    }
    else {
      $this->database->insert('recipe_favorite')
        ->fields([
          'nid' => $node->id(),
          'uid' => $uid,
          'timestamp' => time(),
        ])
        ->execute();
      $favorited = TRUE;
    }
    return new JsonResponse(['status' => 'success', 'favorited' => $favorited]);
  }

}
