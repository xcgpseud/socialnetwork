const state = {
    title: 'Welcome to The Social Network',
};

const getters = {
    pageTitle: state => state.title,
};

const actions = {
    setPageTitle({commit, state}, title) {
        commit('setTitle', title);
    },
};

const mutations = {
    setTitle(state, title) {
        state.title = `${title} | Vengeful`;
        document.title = state.title;
    },
};

export default {state, getters, actions, mutations}
