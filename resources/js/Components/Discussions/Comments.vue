<script setup>
import { ref, watch } from 'vue';
import CommentList from '@/Components/Discussions/CommentList.vue';
import NewComment from '@/Components/Discussions/NewComment.vue';

const props = defineProps({
    comments: Array | null,
    commentableId: Number,
    commentableType: String
});

const emit = defineEmits(['commentAdded']);

const commentItems = ref(props.comments ?? []);

const onRemove = async (commentId) => {
    const response = await axios.delete(route('comment.destroy', commentId));
    console.log(response.data);
};

const onSave = async (comment) => {
    const response = await axios.post(route('comment.store'), {
        comment,
        commentable_id: props.commentableId,
        commentable_type: props.commentableType
    });
    commentItems.value = [
        response.data,
        ...commentItems.value
    ];

    emit('commentAdded', response.data);
};

const onCallAdded = (newCallResponse) => {
    companyCalls.value = newCallResponse.company.calls;
};

watch(() => props.comments, (comments) => {
    if (comments) {
        commentItems.value = [];
        setTimeout(() => {
            commentItems.value = comments;
        }, 1);
    }
});
</script>

<template>
    <div>
        <NewComment @save-comment="onSave" />
        <CommentList :comments="commentItems" @remove-comment="onRemove" />
    </div>
</template>