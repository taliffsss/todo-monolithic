import { createStore } from 'vuex';
import auth from './modules/auth';
import tasks from './modules/tasks';
import tags from './modules/tags';

export default createStore({
    modules: {
        auth,
        tasks,
        tags
    }
});