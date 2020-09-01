<template>
    <div class="flex flex-col items-center py-4">
        <NewPost></NewPost>

        <p v-if="loading">
            Loading Posts...
        </p>
        <Post
            v-else
            v-for="post in posts.data"
            :key="post.data.post_id"
            :post="post"
        ></Post>
    </div>
</template>

<script>
    import NewPost from "../components/NewPost";
    import Post from '../components/Post';


    export default {
        name: "NewsFeed",

        components: {
            NewPost,
            Post,
        },

        data: () => {
            return {
                posts: null,
                loading: true,
            }
        },

        mounted() {
            axios.get('/api/posts')
                .then(res => {
                    this.posts = res.data;
                })
                .catch(error => {
                    console.log('Unable to fetch posts.');
                })
                .finally(() => {
                    this.loading = false;
                });
        }
    }
</script>

<style scoped>

</style>
