import { startStimulusApp } from '@symfony/stimulus-bundle';

export const app = startStimulusApp();

// Register analytics controller from the bundle
import AnalyticsController from '@xutim/analytics-bundle/dist/analytics_controller.js';
app.register('analytics', AnalyticsController);
