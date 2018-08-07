<?php

namespace App\Models;

/**
 * App\Models\Topic
 *
 * @property-read \App\Models\Category $category
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Model ordered()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic recent()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic recentReplied()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic withOrder($order)
 * @mixin \Eloquent
 * @property int $id
 * @property string $title
 * @property string $body
 * @property int $user_id
 * @property int $category_id
 * @property int $reply_count
 * @property int $view_count
 * @property int $last_reply_user_id
 * @property int $order
 * @property string $excerpt
 * @property string|null $slug
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereLastReplyUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereReplyCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Topic whereViewCount($value)
 */
class Topic extends Model
{
	protected $fillable = ['title', 'body', 'category_id', 'excerpt', 'slug'];

	public function category()
	{
		return $this->belongsTo(Category::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function scopeWithOrder($query, $order)
	{
		// 不同的排序，使用不同的数据读取逻辑
		switch ($order) {
			case 'recent':
				$query->recent();
				break;

			default:
				$query->recentReplied();
				break;
		}
		// 预加载防止 N+1 问题
		return $query->with('user', 'category');
	}

	public function scopeRecentReplied($query)
	{
		// 当话题有新回复时，我们将编写逻辑来更新话题模型的 reply_count 属性，
		// 此时会自动触发框架对数据模型 updated_at 时间戳的更新
		return $query->orderBy('updated_at', 'desc');
	}

	public function scopeRecent($query)
	{
		// 按照创建时间排序
		return $query->orderBy('created_at', 'desc');
	}
}
