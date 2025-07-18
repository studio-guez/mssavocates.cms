<?php

namespace Kirby\Panel;

use Closure;
use Kirby\Cms\File as CmsFile;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Filesystem\Asset;
use Kirby\Form\Fields;
use Kirby\Http\Uri;
use Kirby\Toolkit\A;

/**
 * Provides information about the model for the Panel
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class Model
{
	public function __construct(
		protected ModelWithContent $model
	) {
	}

	/**
	 * Returns header button names which should be displayed
	 */
	abstract public function buttons(): array;

	/**
	 * Get the content values for the model
	 *
	 * @deprecated 5.0.0 Use `self::versions()` instead
	 */
	public function content(): array
	{
		return $this->versions()['changes'];
	}

	/**
	 * Returns the drag text from a custom callback
	 * if the callback is defined in the config
	 * @internal
	 *
	 * @param string $type markdown or kirbytext
	 */
	public function dragTextFromCallback(string $type, ...$args): string|null
	{
		$option   = 'panel.' . $type . '.' . $this->model::CLASS_ALIAS . 'DragText';
		$callback = $this->model->kirby()->option($option);

		if ($callback instanceof Closure) {
			return $callback($this->model, ...$args);
		}

		return null;
	}

	/**
	 * Returns the correct drag text type
	 * depending on the given type or the
	 * configuration
	 *
	 * @internal
	 *
	 * @param string|null $type (`auto`|`kirbytext`|`markdown`)
	 */
	public function dragTextType(string|null $type = 'auto'): string
	{
		$type ??= 'auto';

		if ($type === 'auto') {
			$kirby = $this->model->kirby();
			$type  = $kirby->option('panel.kirbytext', true) ? 'kirbytext' : 'markdown';
		}

		return $type === 'markdown' ? 'markdown' : 'kirbytext';
	}

	/**
	 * Returns the setup for a dropdown option
	 * which is used in the changes dropdown
	 * for example.
	 */
	public function dropdownOption(): array
	{
		return [
			'icon'  => 'page',
			'image' => $this->image(['back' => 'black']),
			'link'  => $this->url(true),
			'text'  => $this->model->id(),
		];
	}

	/**
	 * Returns the Panel image definition
	 */
	public function image(
		string|array|false|null $settings = [],
		string $layout = 'list'
	): array|null {
		// completely switched off
		if ($settings === false) {
			return null;
		}

		// switched off from blueprint,
		// only if not overwritten by $settings
		$blueprint = $this->model->blueprint()->image();

		if ($blueprint === false) {
			if (empty($settings) === true) {
				return null;
			}

			$blueprint = null;
		}

		// convert string blueprint settings to proper array
		if (is_string($blueprint) === true) {
			$blueprint = ['query' => $blueprint];
		}

		// skip image thumbnail if option
		// is explicitly set to show the icon
		if ($settings === 'icon') {
			$settings = ['query' => false];
		}

		// convert string settings to proper array
		if (is_string($settings) === true) {
			$settings = ['query' => $settings];
		}

		// merge with defaults and blueprint option
		$settings = [
			...$this->imageDefaults(),
			...$settings ?? [],
			...$blueprint ?? [],
		];

		if ($image = $this->imageSource($settings['query'] ?? null)) {
			// main url
			$settings['url'] = $image->url();

			if ($image->isResizable() === true) {
				// only create srcsets for resizable files
				$settings['src']    = static::imagePlaceholder();
				$settings['srcset'] = $this->imageSrcset($image, $layout, $settings);
			} elseif ($image->isViewable() === true) {
				$settings['src'] = $image->url();
			}
		}

		unset($settings['query']);

		// resolve remaining options defined as query
		return A::map($settings, function ($option) {
			if (is_string($option) === false) {
				return $option;
			}

			return $this->model->toString($option);
		});
	}

	/**
	 * Default settings for Panel image
	 */
	protected function imageDefaults(): array
	{
		return [
			'back'  => 'pattern',
			'color' => 'gray-500',
			'cover' => false,
			'icon'  => 'page'
		];
	}

	/**
	 * Data URI placeholder string for Panel image
	 */
	public static function imagePlaceholder(): string
	{
		return 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw';
	}

	/**
	 * Returns the image file object based on provided query
	 */
	protected function imageSource(
		string|null $query = null
	): CmsFile|Asset|null {
		$image = $this->model->query($query ?? null);

		// validate the query result
		if (
			$image instanceof CmsFile ||
			$image instanceof Asset
		) {
			return $image;
		}

		return null;
	}

	/**
	 * Provides the correct srcset string based on
	 * the layout and settings
	 */
	protected function imageSrcset(
		CmsFile|Asset $image,
		string $layout,
		array $settings
	): string|null {
		// depending on layout type, set different sizes
		// to have multiple options for the srcset attribute
		$sizes = match ($layout) {
			'cards'    => [352, 864, 1408],
			'cardlets' => [96, 192],
			default    => [38, 76]
		};

		// no additional modfications needed if `cover: false`
		if (($settings['cover'] ?? false) === false) {
			return $image->srcset($sizes);
		}

		// for card layouts with `cover: true` provide
		// crops based on the card ratio
		if ($layout === 'cards') {
			$ratio = $settings['ratio'] ?? '1/1';

			if (is_numeric($ratio) === false) {
				$ratio = explode('/', $ratio);
				$ratio = $ratio[0] / $ratio[1];
			}

			return $image->srcset([
				$sizes[0] . 'w' => [
					'width'  => $sizes[0],
					'height' => round($sizes[0] / $ratio),
					'crop'   => true
				],
				$sizes[1] . 'w' => [
					'width'  => $sizes[1],
					'height' => round($sizes[1] / $ratio),
					'crop'   => true
				],
				$sizes[2] . 'w' => [
					'width'  => $sizes[2],
					'height' => round($sizes[2] / $ratio),
					'crop'   => true
				]
			]);
		}

		// for list and cardlets with `cover: true`
		// provide square crops in two resolutions
		return $image->srcset([
			'1x' => [
				'width'  => $sizes[0],
				'height' => $sizes[0],
				'crop'   => true
			],
			'2x' => [
				'width'  => $sizes[1],
				'height' => $sizes[1],
				'crop'   => true
			]
		]);
	}

	/**
	 * Checks for disabled dropdown options according
	 * to the given permissions
	 */
	public function isDisabledDropdownOption(
		string $action,
		array $options,
		array $permissions
	): bool {
		$option = $options[$action] ?? true;

		return
			$permissions[$action] === false ||
			$option === false ||
			$option === 'false';
	}

	/**
	 * Returns the corresponding model object
	 * @since 5.0.0
	 */
	public function model(): ModelWithContent
	{
		return $this->model;
	}

	/**
	 * Returns an array of all actions
	 * that can be performed in the Panel
	 * This also checks for the lock status
	 *
	 * @param array $unlock An array of options that will be force-unlocked
	 */
	public function options(array $unlock = []): array
	{
		$options = $this->model->permissions()->toArray();

		if ($this->model->lock()->isLocked() === true) {
			foreach ($options as $key => $value) {
				if (in_array($key, $unlock, true)) {
					continue;
				}

				$options[$key] = false;
			}
		}

		return $options;
	}

	/**
	 * Returns the full path without leading slash
	 */
	abstract public function path(): string;

	/**
	 * Prepares the response data for page pickers
	 * and page fields
	 */
	public function pickerData(array $params = []): array
	{
		return [
			'id'       => $this->model->id(),
			'image'    => $this->image(
				$params['image'] ?? [],
				$params['layout'] ?? 'list'
			),
			'info'     => $this->model->toSafeString($params['info'] ?? false),
			'link'     => $this->url(true),
			'sortable' => true,
			'text'     => $this->model->toSafeString($params['text'] ?? false),
			'uuid'     => $this->model->uuid()?->toString()
		];
	}

	/**
	 * Returns the data array for the view's component props
	 */
	public function props(): array
	{
		$blueprint = $this->model->blueprint();
		$link      = $this->url(true);
		$request   = $this->model->kirby()->request();
		$tabs      = $blueprint->tabs();
		$tab       = $blueprint->tab($request->get('tab')) ?? $tabs[0] ?? null;
		$versions  = $this->versions();

		$props = [
			'api'         => $link,
			'buttons'     => fn () => $this->buttons(),
			'id'          => $this->model->id(),
			'link'        => $link,
			'lock'        => $this->model->lock()->toArray(),
			'permissions' => $this->model->permissions()->toArray(),
			'tabs'        => $tabs,
			'uuid'        => fn () => $this->model->uuid()?->toString(),
			'versions'    => [
				'latest'  => (object)$versions['latest'],
				'changes' => (object)$versions['changes']
			]
		];

		// only send the tab if it exists
		// this will let the vue component define
		// a proper default value
		if ($tab) {
			$props['tab'] = $tab;
		}

		return $props;
	}

	/**
	 * Returns link url and title
	 * for model (e.g. used for prev/next navigation)
	 */
	public function toLink(string $title = 'title'): array
	{
		return [
			'link'    => $this->url(true),
			'title'   => $title = (string)$this->model->{$title}()
		];
	}

	/**
	 * Returns link url and title
	 * for optional sibling model and
	 * preserves tab selection
	 */
	protected function toPrevNextLink(
		ModelWithContent|null $model = null,
		string $title = 'title'
	): array|null {
		if ($model === null) {
			return null;
		}

		$data = $model->panel()->toLink($title);

		if ($tab = $model->kirby()->request()->get('tab')) {
			$uri = new Uri($data['link'], [
				'query' => ['tab' => $tab]
			]);

			$data['link'] = $uri->toString();
		}

		return $data;
	}

	/**
	 * Returns the url to the editing view
	 * in the Panel
	 */
	public function url(bool $relative = false): string
	{
		if ($relative === true) {
			return '/' . $this->path();
		}

		return $this->model->kirby()->url('panel') . '/' . $this->path();
	}

	/**
	 * Creates an array with two versions of the content:
	 * `latest` and `changes`.
	 *
	 * The content is passed through the Fields class
	 * to ensure that the content is in the correct format
	 * for the Panel. If there's no `changes` version, the `latest`
	 * version is used for both.
	 */
	public function versions(): array
	{
		$language = Language::ensure('current');
		$fields   = Fields::for($this->model, $language);

		$latestVersion  = $this->model->version('latest');
		$changesVersion = $this->model->version('changes');

		$latestContent  = $latestVersion->content($language)->toArray();
		$changesContent = $latestContent;

		if ($changesVersion->exists($language) === true) {
			$changesContent = $changesVersion->content($language)->toArray();
		}

		return [
			'latest'  => $fields->reset()->fill($latestContent)->toFormValues(),
			'changes' => $fields->reset()->fill($changesContent)->toFormValues()
		];
	}

	/**
	 * Returns the data array for this model's Panel view
	 */
	abstract public function view(): array;
}
