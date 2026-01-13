import { startStimulusApp } from '@symfony/stimulus-bundle';

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp();

// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);

import ArticlePreview from './controllers/article-preview_controller.js';
import AutoExpand from './controllers/auto-expand_controller.js';
import AutoSubmit from './controllers/auto-submit_controller.js';
import Clipboard from './controllers/clipboard_controller.js';
import DateTime from './controllers/date-time_controller.js';
import DialogModal from './controllers/dialog-modal_controller.js';
import EditorjsForm from './controllers/editorjs-form_controller.js';
import FeaturedImage from './controllers/featured-image_controller.js';
import Format from './controllers/format_controller.js';
import Lightbox from './controllers/lightbox_controller.js';
import MediaField from './controllers/media-field_controller.js';
import MenuItem from './controllers/menu-item_controller.js';
import ModalForm from './controllers/modal-form_controller.js';
import Modal from './controllers/modal_controller.js';
import PlainModal from './controllers/plain-modal_controller.js';
import Popover from './controllers/popover_controller.js';
import ReloadFrame from './controllers/reload-frame_controller.js';
import RevisionFilter from './controllers/revision-filter_controller.js';
import SlugGenerator from './controllers/slug-generator_controller.js';
import Sortable from './controllers/sortable_controller.js';
import TagMenu from './controllers/tag-menu_controller.js';
import TomSelect from './controllers/tom-select_controller.js';
import Tooltip from './controllers/tooltip_controller.js';
import TranslationRow from './controllers/translation-row_controller.js';
import Sidebar from './controllers/sidebar_controller.js';
import SnippetEditor from './controllers/snippet-editor_controller.js';
import Draggable from './controllers/draggable_controller.js';
import DropTarget from './controllers/drop-target_controller.js';
import SplitView from './controllers/split-view_controller.js';

app.register('sidebar', Sidebar);
app.register('article-preview', ArticlePreview);
app.register('auto-expand', AutoExpand);
app.register('auto-submit', AutoSubmit);
app.register('clipboard', Clipboard);
app.register('date-time', DateTime);
app.register('dialog-modal', DialogModal);
app.register('editorjs-form', EditorjsForm);
app.register('featured-image', FeaturedImage);
app.register('format', Format);
app.register('lightbox', Lightbox);
app.register('media-field', MediaField);
app.register('menu-item', MenuItem);
app.register('modal-form', ModalForm);
app.register('modal', Modal);
app.register('plain-modal', PlainModal);
app.register('popover', Popover);
app.register('reload-frame', ReloadFrame);
app.register('revision-filter', RevisionFilter);
app.register('slug-generator', SlugGenerator);
app.register('sortable', Sortable);
app.register('tag-menu', TagMenu);
app.register('tom-select', TomSelect);
app.register('tooltip', Tooltip);
app.register('translation-row', TranslationRow);
app.register('snippet-editor', SnippetEditor);
app.register('draggable', Draggable);
app.register('drop-target', DropTarget);
app.register('split-view', SplitView);
